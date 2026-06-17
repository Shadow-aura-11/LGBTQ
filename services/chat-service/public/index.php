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

Database::connect('matrimony_chat');

// Schema migration for messages
if (!Capsule::schema()->hasTable('messages')) {
    Capsule::schema()->create('messages', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('sender_id');
        $table->unsignedBigInteger('recipient_id');
        $table->text('message');
        $table->boolean('is_read')->default(false);
        $table->timestamp('read_at')->nullable();
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

// Require premium tier to initiate chat operations
$premiumMiddleware = new AuthMiddleware('premium');
$authMiddleware = new AuthMiddleware();

// Get Message History
$app->get('/api/v1/chats/history/{recipient_id}', function (Request $request, Response $response, array $args) {
    $user = $request->getAttribute('user');
    $recipientId = (int)$args['recipient_id'];

    // Retrieve conversation history
    $history = Capsule::table('messages')
        ->where(function($q) use ($user, $recipientId) {
            $q->where('sender_id', $user['id'])->where('recipient_id', $recipientId);
        })
        ->orWhere(function($q) use ($user, $recipientId) {
            $q->where('sender_id', $recipientId)->where('recipient_id', $user['id']);
        })
        ->orderBy('created_at', 'asc')
        ->get();

    $response->getBody()->write(json_encode([
        'success' => true,
        'history' => $history
    ]));
    return $response->withHeader('Content-Type', 'application/json');
})->add($premiumMiddleware);

// Send Message
$app->post('/api/v1/chats/send', function (Request $request, Response $response) {
    $user = $request->getAttribute('user');
    $data = json_decode($request->getBody()->getContents(), true);

    if (empty($data['recipient_id']) || empty($data['message'])) {
        $response->getBody()->write(json_encode(['success' => false, 'error' => 'Recipient ID and message content are required.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $recipientId = (int)$data['recipient_id'];
    $messageText = trim($data['message']);

    $messageId = Capsule::table('messages')->insertGetId([
        'sender_id' => $user['id'],
        'recipient_id' => $recipientId,
        'message' => $messageText,
        'is_read' => false,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);

    $messagePayload = [
        'id' => $messageId,
        'sender_id' => $user['id'],
        'sender_name' => $user['name'],
        'recipient_id' => $recipientId,
        'message' => $messageText,
        'created_at' => date('Y-m-d H:i:s')
    ];

    // Emit event on internal pub/sub event bus
    EventBus::publish('message.sent', $messagePayload);

    $response->getBody()->write(json_encode([
        'success' => true,
        'message' => $messagePayload
    ]));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
})->add($premiumMiddleware);

// Mark Messages as Read
$app->post('/api/v1/chats/read/{message_id}', function (Request $request, Response $response, array $args) {
    $user = $request->getAttribute('user');
    $messageId = (int)$args['message_id'];

    Capsule::table('messages')
        ->where('id', $messageId)
        ->where('recipient_id', $user['id'])
        ->update([
            'is_read' => true,
            'read_at' => date('Y-m-d H:i:s')
        ]);

    // Emit event for read receipt update
    EventBus::publish('message.read', [
        'message_id' => $messageId,
        'reader_id' => $user['id']
    ]);

    $response->getBody()->write(json_encode(['success' => true]));
    return $response->withHeader('Content-Type', 'application/json');
});

// Get unique chatted user IDs list
$app->get('/api/v1/chats/conversations', function (Request $request, Response $response) {
    $user = $request->getAttribute('user');
    
    $messages = Capsule::table('messages')
        ->where('sender_id', $user['id'])
        ->orWhere('recipient_id', $user['id'])
        ->get();
        
    $userIds = [];
    foreach ($messages as $m) {
        if ($m->sender_id !== $user['id']) {
            $userIds[] = (int)$m->sender_id;
        }
        if ($m->recipient_id !== $user['id']) {
            $userIds[] = (int)$m->recipient_id;
        }
    }
    $userIds = array_values(array_unique($userIds));
    
    $response->getBody()->write(json_encode([
        'success' => true,
        'userIds' => $userIds
    ]));
    return $response->withHeader('Content-Type', 'application/json');
})->add($premiumMiddleware);

$app->run();
