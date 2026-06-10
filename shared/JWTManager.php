<?php

namespace Shared;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Predis\Client as RedisClient;

class JWTManager {
    protected static function getRedis() {
        $host = getenv('REDIS_HOST') ?: 'redis';
        $port = getenv('REDIS_PORT') ?: 6379;
        return new RedisClient([
            'scheme' => 'tcp',
            'host'   => $host,
            'port'   => $port,
        ]);
    }

    public static function issue(array $user) {
        $secret = getenv('JWT_SECRET') ?: 'default_secret';
        $accessExpiry = (int)(getenv('JWT_ACCESS_EXPIRY') ?: 900);
        $refreshExpiry = (int)(getenv('JWT_REFRESH_EXPIRY') ?: 604800);

        $now = time();

        $accessPayload = [
            'iss'  => 'lgbtq-matrimony-auth',
            'aud'  => 'lgbtq-matrimony-platform',
            'iat'  => $now,
            'nbf'  => $now,
            'exp'  => $now + $accessExpiry,
            'user' => [
                'id'    => $user['id'],
                'email' => $user['email'],
                'name'  => $user['name'],
                'role'  => $user['role'] ?? 'user',
                'tier'  => $user['tier'] ?? 'free'
            ]
        ];

        $refreshPayload = [
            'iss'  => 'lgbtq-matrimony-auth',
            'aud'  => 'lgbtq-matrimony-platform',
            'iat'  => $now,
            'nbf'  => $now,
            'exp'  => $now + $refreshExpiry,
            'user_id' => $user['id']
        ];

        return [
            'access_token'  => JWT::encode($accessPayload, $secret, 'HS256'),
            'refresh_token' => JWT::encode($refreshPayload, $secret, 'HS256'),
            'expires_in'    => $accessExpiry
        ];
    }

    public static function verify($token) {
        $secret = getenv('JWT_SECRET') ?: 'default_secret';
        
        try {
            // Check Redis Blacklist
            $redis = self::getRedis();
            if ($redis->get("blacklist:" . md5($token))) {
                return null;
            }

            $decoded = JWT::decode($token, new Key($secret, 'HS256'));
            return (array)$decoded->user;
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function blacklist($token, $expirySeconds = 3600) {
        $redis = self::getRedis();
        $redis->setex("blacklist:" . md5($token), $expirySeconds, '1');
    }
}
