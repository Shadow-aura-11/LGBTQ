<?php

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Shared\JWTManager;
use Predis\Async\Client as AsyncRedis;
use React\EventLoop\Loop;

require __DIR__ . '/../vendor/autoload';

// Load Environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

class WebSocketChatHandler implements MessageComponentInterface {
    protected $clients;
    protected $userConnections; // userId => ConnectionInterface

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->userConnections = [];
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);

        // Authenticate connection via query parameter token
        $queryString = $conn->httpRequest->getUri()->getQuery();
        parse_str($queryString, $queryParams);
        $token = $queryParams['token'] ?? null;

        if ($token) {
            $user = JWTManager::verify($token);
            if ($user) {
                $conn->user = $user;
                $this->userConnections[$user['id']] = $conn;
                
                // Broadcast online status to mutual connections or everyone
                $this->broadcastToAll([
                    'type' => 'status',
                    'user_id' => $user['id'],
                    'status' => 'online'
                ]);
                
                echo "User {$user['id']} ({$user['name']}) connected.\n";
                return;
            }
        }

        // Close unauthenticated connection
        echo "Rejected unauthenticated connection.\n";
        $conn->send(json_encode(['error' => 'Unauthenticated']));
        $conn->close();
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        if (!$data || !isset($from->user)) {
            return;
        }

        $userId = $from->user['id'];

        switch ($data['type'] ?? '') {
            case 'typing':
                $targetId = (int)($data['recipient_id'] ?? 0);
                if (isset($this->userConnections[$targetId])) {
                    $this->userConnections[$targetId]->send(json_encode([
                        'type' => 'typing',
                        'sender_id' => $userId,
                        'typing' => (bool)($data['typing'] ?? false)
                    ]));
                }
                break;

            case 'read_receipt':
                $senderId = (int)($data['sender_id'] ?? 0);
                $messageId = (int)($data['message_id'] ?? 0);
                if (isset($this->userConnections[$senderId])) {
                    $this->userConnections[$senderId]->send(json_encode([
                        'type' => 'read_receipt',
                        'message_id' => $messageId,
                        'recipient_id' => $userId
                    ]));
                }
                break;
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);

        if (isset($conn->user)) {
            $userId = $conn->user['id'];
            unset($this->userConnections[$userId]);

            // Broadcast offline status
            $this->broadcastToAll([
                'type' => 'status',
                'user_id' => $userId,
                'status' => 'offline'
            ]);
            echo "User {$userId} disconnected.\n";
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "WebSocket Error: {$e->getMessage()}\n";
        $conn->close();
    }

    public function sendToUser($userId, array $payload) {
        if (isset($this->userConnections[$userId])) {
            $this->userConnections[$userId]->send(json_encode($payload));
            return true;
        }
        return false;
    }

    protected function broadcastToAll(array $payload) {
        $raw = json_encode($payload);
        foreach ($this->clients as $client) {
            $client->send($raw);
        }
    }
}

// Start Ratchet server & react event loop
$appHandler = new WebSocketChatHandler();
$loop = Loop::get();

// Setup WebSocket server
$socketServer = new \React\Socket\SocketServer('0.0.0.0:8080', [], $loop);
$wsServer = new \Ratchet\Server\IoServer(
    new \Ratchet\Http\HttpServer(
        new \Ratchet\WebSocket\WsServer($appHandler)
    ),
    $socketServer,
    $loop
);

// Redis Sub in separate async context using Predis with react event loop
$redisHost = getenv('REDIS_HOST') ?: 'redis';
$redisPort = getenv('REDIS_PORT') ?: 6379;

// Run subscriber connection to listen to Redis event bus
$loop->futureTick(function() use ($appHandler, $redisHost, $redisPort) {
    try {
        $redis = new Predis\Client([
            'scheme' => 'tcp',
            'host'   => $redisHost,
            'port'   => $redisPort,
            'read_write_timeout' => 0
        ]);
        
        $pubsub = $redis->pubSubLoop();
        $pubsub->subscribe(['message.sent', 'notification.broadcast', 'subscription.activated']);

        echo "WebSocket listener subscribed to Redis pub/sub channels.\n";

        // Periodically pull from pubsub loop non-blocking
        Loop::addPeriodicTimer(0.1, function() use ($pubsub, $appHandler) {
            foreach ($pubsub as $message) {
                if ($message->kind === 'message') {
                    $payload = json_decode($message->payload, true);
                    
                    if ($message->channel === 'message.sent') {
                        // Deliver instant message if recipient online
                        $recipientId = (int)$payload['recipient_id'];
                        $appHandler->sendToUser($recipientId, [
                            'type' => 'message',
                            'message' => $payload
                        ]);
                    } elseif ($message->channel === 'notification.broadcast') {
                        // Deliver in-app notification
                        $userId = (int)$payload['user_id'];
                        $appHandler->sendToUser($userId, [
                            'type' => 'notification',
                            'notification' => $payload
                        ]);
                    }
                }
            }
        });
    } catch (\Exception $e) {
        echo "Redis pub/sub error: " . $e->getMessage() . "\n";
    }
});

echo "WebSocket Server running on port 8080...\n";
$wsServer->run();
