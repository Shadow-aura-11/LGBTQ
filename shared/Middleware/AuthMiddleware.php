<?php

namespace Shared\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as SlimResponse;
use Shared\JWTManager;

class AuthMiddleware {
    protected $requiredTier;

    public function __construct($requiredTier = 'free') {
        $this->requiredTier = $requiredTier;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response {
        $authHeader = $request->getHeaderLine('Authorization');
        $token = null;

        // Try extracting from Authorization header
        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
        }

        // Fallback: check cookie for frontend pages
        if (!$token) {
            $cookies = $request->getCookieParams();
            $token = $cookies['jwt_token'] ?? null;
        }

        if (!$token) {
            return $this->unauthorizedResponse("Authorization token missing.");
        }

        $user = JWTManager::verify($token);
        if (!$user) {
            return $this->unauthorizedResponse("Invalid or expired token.");
        }

        // Subscription Tier Verification
        if ($this->requiredTier === 'premium' && ($user['tier'] ?? 'free') !== 'premium') {
            $response = new SlimResponse();
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => 'Premium subscription required for this feature.',
                'code' => 'PREMIUM_REQUIRED'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
        }

        // Admin Role Verification
        if ($this->requiredTier === 'admin' && ($user['role'] ?? 'user') !== 'admin') {
            $response = new SlimResponse();
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => 'Admin access required.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
        }

        // Add user context to request attributes
        $request = $request->withAttribute('user', $user);
        return $handler->handle($request);
    }

    protected function unauthorizedResponse($message) {
        $response = new SlimResponse();
        $response->getBody()->write(json_encode([
            'success' => false,
            'error' => $message
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }
}
