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

Database::connect('matrimony_moderation');

// Schema migration
if (!Capsule::schema()->hasTable('reports')) {
    Capsule::schema()->create('reports', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('reporter_id');
        $table->unsignedBigInteger('reported_id');
        $table->text('reason');
        $table->string('status')->default('pending'); // pending, reviewed, dismissed
        $table->string('action_taken')->nullable(); // none, suspended
        $table->timestamps();
    });
}

if (!Capsule::schema()->hasTable('blocks')) {
    Capsule::schema()->create('blocks', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('blocker_id');
        $table->unsignedBigInteger('blocked_id');
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
$adminMiddleware = new AuthMiddleware('admin');

// Submit User Report
$app->post('/api/v1/moderation/report', function (Request $request, Response $response) {
    $user = $request->getAttribute('user');
    $data = json_decode($request->getBody()->getContents(), true);

    if (empty($data['reported_id']) || empty($data['reason'])) {
        $response->getBody()->write(json_encode(['success' => false, 'error' => 'Reported user ID and reason are required.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $reportedId = (int)$data['reported_id'];
    $reason = trim($data['reason']);

    Capsule::table('reports')->insert([
        'reporter_id' => $user['id'],
        'reported_id' => $reportedId,
        'reason' => $reason,
        'status' => 'pending',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);

    // Send notification event to alerts for admins if needed
    EventBus::publish('report.submitted', [
        'reporter_id' => $user['id'],
        'reported_id' => $reportedId,
        'reason' => $reason
    ]);

    $response->getBody()->write(json_encode(['success' => true, 'message' => 'Profile reported successfully. It will be reviewed by administrators.']));
    return $response->withHeader('Content-Type', 'application/json');
})->add($authMiddleware);

// Block User
$app->post('/api/v1/moderation/block', function (Request $request, Response $response) {
    $user = $request->getAttribute('user');
    $data = json_decode($request->getBody()->getContents(), true);

    if (empty($data['blocked_id'])) {
        $response->getBody()->write(json_encode(['success' => false, 'error' => 'Target user ID to block is required.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $blockedId = (int)$data['blocked_id'];

    $exists = Capsule::table('blocks')
        ->where('blocker_id', $user['id'])
        ->where('blocked_id', $blockedId)
        ->exists();

    if (!$exists) {
        Capsule::table('blocks')->insert([
            'blocker_id' => $user['id'],
            'blocked_id' => $blockedId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    $response->getBody()->write(json_encode(['success' => true, 'message' => 'User blocked.']));
    return $response->withHeader('Content-Type', 'application/json');
})->add($authMiddleware);

// Admin: Get Moderation queue
$app->get('/api/v1/moderation/admin/reports', function (Request $request, Response $response) {
    $reports = Capsule::table('reports')->orderBy('id', 'desc')->get();
    
    $response->getBody()->write(json_encode([
        'success' => true,
        'reports' => $reports
    ]));
    return $response->withHeader('Content-Type', 'application/json');
})->add($adminMiddleware);

// Admin: Resolve Report
$app->post('/api/v1/moderation/admin/reports/{id}/resolve', function (Request $request, Response $response, array $args) {
    $id = (int)$args['id'];
    $data = json_decode($request->getBody()->getContents(), true);

    $action = $data['action'] ?? 'dismissed'; // dismissed, suspended

    $report = Capsule::table('reports')->where('id', $id)->first();
    if (!$report) {
        $response->getBody()->write(json_encode(['success' => false, 'error' => 'Report not found.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }

    Capsule::table('reports')->where('id', $id)->update([
        'status' => 'reviewed',
        'action_taken' => $action,
        'updated_at' => date('Y-m-d H:i:s')
    ]);

    if ($action === 'suspended') {
        // Suspend user by publishing ban event
        EventBus::publish('user.suspended', [
            'user_id' => $report->reported_id
        ]);
    }

    $response->getBody()->write(json_encode(['success' => true, 'message' => "Report resolved as {$action}."]));
    return $response->withHeader('Content-Type', 'application/json');
})->add($adminMiddleware);

$app->run();
