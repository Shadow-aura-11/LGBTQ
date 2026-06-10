<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Shared\Database;
use Shared\Middleware\AuthMiddleware;
use Shared\EventBus;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as Capsule;

require __DIR__ . '/../../../vendor/autoload';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../../');
$dotenv->safeLoad();

Database::connect('matrimony_activity');

// Schema migration for activities
if (!Capsule::schema()->hasTable('activity_logs')) {
    Capsule::schema()->create('activity_logs', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id'); // initiator
        $table->unsignedBigInteger('target_id'); // recipient
        $table->string('action_type'); // view, interest
        $table->string('status')->default('pending'); // pending, accepted, rejected
        $table->timestamps();
    });
}

$app = AppFactory::create();
$app->addRoutingMiddleware();

// Handle CORS
$app->add(function (Request $request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

$authMiddleware = new AuthMiddleware();
$premiumMiddleware = new AuthMiddleware('premium');

// Send Interest (Premium-only)
$app->post('/api/v1/activity/interest', function (Request $request, Response $response) {
    $user = $request->getAttribute('user');
    $data = json_decode($request->getBody()->getContents(), true);

    if (empty($data['target_id'])) {
        $response->getBody()->write(json_encode(['success' => false, 'error' => 'Target user ID is required.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $targetId = (int)$data['target_id'];

    if ($user['id'] === $targetId) {
        $response->getBody()->write(json_encode(['success' => false, 'error' => 'You cannot send an interest to yourself.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    // Check if interest already sent
    $exists = Capsule::table('activity_logs')
        ->where('user_id', $user['id'])
        ->where('target_id', $targetId)
        ->where('action_type', 'interest')
        ->first();

    if ($exists) {
        $response->getBody()->write(json_encode(['success' => false, 'error' => 'You have already sent an interest to this user.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    // Record activity
    $id = Capsule::table('activity_logs')->insertGetId([
        'user_id' => $user['id'],
        'target_id' => $targetId,
        'action_type' => 'interest',
        'status' => 'pending',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);

    // Emit event to notify the target user via notification-service & websocket
    EventBus::publish('interest.sent', [
        'id' => $id,
        'sender_id' => $user['id'],
        'sender_name' => $user['name'],
        'target_id' => $targetId,
        'type' => 'interest'
    ]);

    // Check mutual matching (did target send interest to user before?)
    $mutual = Capsule::table('activity_logs')
        ->where('user_id', $targetId)
        ->where('target_id', $user['id'])
        ->where('action_type', 'interest')
        ->first();

    if ($mutual) {
        // Mark both as accepted
        Capsule::table('activity_logs')->where('id', $id)->update(['status' => 'accepted']);
        Capsule::table('activity_logs')->where('id', $mutual->id)->update(['status' => 'accepted']);
        
        // Notify both of Mutual Match!
        EventBus::publish('match.mutual', [
            'user1_id' => $user['id'],
            'user1_name' => $user['name'],
            'user2_id' => $targetId
        ]);
    }

    $response->getBody()->write(json_encode([
        'success' => true,
        'message' => 'Interest sent successfully.',
        'mutual' => (bool)$mutual
    ]));
    return $response->withHeader('Content-Type', 'application/json');
})->add($premiumMiddleware);

// Get Received Interests
$app->get('/api/v1/activity/interests/received', function (Request $request, Response $response) {
    $user = $request->getAttribute('user');

    $interests = Capsule::table('activity_logs')
        ->where('target_id', $user['id'])
        ->where('action_type', 'interest')
        ->orderBy('created_at', 'desc')
        ->get();

    $response->getBody()->write(json_encode([
        'success' => true,
        'interests' => $interests
    ]));
    return $response->withHeader('Content-Type', 'application/json');
})->add($authMiddleware);

// Accept/Reject Interest
$app->post('/api/v1/activity/interest/{id}/respond', function (Request $request, Response $response, array $args) {
    $user = $request->getAttribute('user');
    $id = (int)$args['id'];
    $data = json_decode($request->getBody()->getContents(), true);

    $actionStatus = $data['status'] ?? 'accepted'; // accepted, rejected

    if (!in_array($actionStatus, ['accepted', 'rejected'])) {
        $response->getBody()->write(json_encode(['success' => false, 'error' => 'Invalid status option.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $interest = Capsule::table('activity_logs')
        ->where('id', $id)
        ->where('target_id', $user['id'])
        ->first();

    if (!$interest) {
        $response->getBody()->write(json_encode(['success' => false, 'error' => 'Interest invitation not found.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }

    Capsule::table('activity_logs')->where('id', $id)->update([
        'status' => $actionStatus,
        'updated_at' => date('Y-m-d H:i:s')
    ]);

    if ($actionStatus === 'accepted') {
        // Create matching logs or emit mutual matching
        EventBus::publish('interest.accepted', [
            'sender_id' => $interest->user_id,
            'recipient_id' => $user['id'],
            'recipient_name' => $user['name']
        ]);
    }

    $response->getBody()->write(json_encode(['success' => true, 'message' => "Interest {$actionStatus}."]));
    return $response->withHeader('Content-Type', 'application/json');
})->add($authMiddleware);

// Get Profile Views
$app->get('/api/v1/activity/views', function (Request $request, Response $response) {
    $user = $request->getAttribute('user');

    $views = Capsule::table('activity_logs')
        ->where('target_id', $user['id'])
        ->where('action_type', 'view')
        ->orderBy('created_at', 'desc')
        ->get();

    $response->getBody()->write(json_encode([
        'success' => true,
        'views' => $views
    ]));
    return $response->withHeader('Content-Type', 'application/json');
})->add($authMiddleware);

// Internal profile view logger (called via Event Bus or sync HTTP API)
$app->post('/api/v1/activity/internal/log-view', function (Request $request, Response $response) {
    $data = json_decode($request->getBody()->getContents(), true);

    if (empty($data['viewer_id']) || empty($data['target_id'])) {
        $response->getBody()->write(json_encode(['success' => false, 'error' => 'Invalid parameters.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $viewerId = (int)$data['viewer_id'];
    $targetId = (int)$data['target_id'];

    Capsule::table('activity_logs')->insert([
        'user_id' => $viewerId,
        'target_id' => $targetId,
        'action_type' => 'view',
        'status' => 'completed',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);

    $response->getBody()->write(json_encode(['success' => true]));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();
