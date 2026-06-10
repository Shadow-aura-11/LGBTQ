<?php

namespace Shared;

use Predis\Client as RedisClient;

class EventBus {
    protected static $client = null;

    protected static function getClient() {
        if (self::$client === null) {
            $host = getenv('REDIS_HOST') ?: 'redis';
            $port = getenv('REDIS_PORT') ?: 6379;
            self::$client = new RedisClient([
                'scheme' => 'tcp',
                'host'   => $host,
                'port'   => $port,
            ]);
        }
        return self::$client;
    }

    public static function publish($channel, array $data) {
        $client = self::getClient();
        $payload = json_encode($data);
        $client->publish($channel, $payload);
    }

    public static function subscribe(array $channels, callable $callback) {
        $host = getenv('REDIS_HOST') ?: 'redis';
        $port = getenv('REDIS_PORT') ?: 6379;
        
        // Use a persistent loop connection for subscriber
        $client = new RedisClient([
            'scheme' => 'tcp',
            'host'   => $host,
            'port'   => $port,
            'read_write_timeout' => 0 // Infinite timeout for long-running CLI tasks
        ]);

        $pubsub = $client->pubSubLoop();
        $pubsub->subscribe($channels);

        foreach ($pubsub as $message) {
            if ($message->kind === 'message') {
                $payload = json_decode($message->payload, true);
                $callback($message->channel, $payload);
            }
        }
    }
}
