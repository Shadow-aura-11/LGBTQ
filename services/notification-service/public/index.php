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

Database::connect('matrimony_notification');

// Schema migration for notifications
if (!Capsule::schema()->hasTable('notifications')) {
    Capsule::schema()->create('notifications', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->string('title');
        $table->text('message');
        $table->string('type'); // like, profile_view, message
        $table->boolean('is_read')->default(false);
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

// Fetch Notifications
$app->get('/api/v1/notifications', function (Request $request, Response $response) {
    $user = $request->getAttribute('user');
    
    $notifications = Capsule::table('notifications')
        ->where('user_id', $user['id'])
        ->orderBy('id', 'desc')
        ->limit(50)
        ->get();

    $response->getBody()->write(json_encode([
        'success' => true,
        'notifications' => $notifications
    ]));
    return $response->withHeader('Content-Type', 'application/json');
})->add($authMiddleware);

// Mark read
$app->post('/api/v1/notifications/{id}/read', function (Request $request, Response $response, array $args) {
    $user = $request->getAttribute('user');
    $id = (int)$args['id'];

    Capsule::table('notifications')
        ->where('id', $id)
        ->where('user_id', $user['id'])
        ->update(['is_read' => true]);

    $response->getBody()->write(json_encode(['success' => true]));
    return $response->withHeader('Content-Type', 'application/json');
})->add($authMiddleware);

// Internal endpoint to create notifications manually or from background listener
$app->post('/api/v1/notifications/internal/create', function (Request $request, Response $response) {
    $data = json_decode($request->getBody()->getContents(), true);

    if (empty($data['user_id']) || empty($data['title']) || empty($data['message'])) {
        $response->getBody()->write(json_encode(['success' => false, 'error' => 'Missing fields.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $id = Capsule::table('notifications')->insertGetId([
        'user_id' => (int)$data['user_id'],
        'title' => $data['title'],
        'message' => $data['message'],
        'type' => $data['type'] ?? 'alert',
        'is_read' => false,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);

    // Perform mock SMTP and FCM delivery
    $emailLog = "MOCK EMAIL SENT TO User {$data['user_id']}: [{$data['title']}] - {$data['message']}\n";
    $fcmLog = "MOCK FCM PUSH SENT TO User {$data['user_id']}: [{$data['title']}] - {$data['message']}\n";
    
    file_put_contents(__DIR__ . '/../../../scratch/notification_mock.log', $emailLog . $fcmLog, FILE_APPEND);

    // Publish event for real-time WebSocket connection to receive it immediately
    EventBus::publish('notification.broadcast', [
        'user_id' => (int)$data['user_id'],
        'id' => $id,
        'title' => $data['title'],
        'message' => $data['message'],
        'type' => $data['type'] ?? 'alert',
        'created_at' => date('Y-m-d H:i:s')
    ]);

    $response->getBody()->write(json_encode(['success' => true, 'id' => $id]));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();
