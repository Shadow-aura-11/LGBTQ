<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Shared\Database;
use Shared\JWTManager;
use Shared\EventBus;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as Capsule;

require __DIR__ . '/../../../vendor/autoload';

// Load Environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../../');
$dotenv->safeLoad();

// Initialize DB Connection
Database::connect('matrimony_auth');

// Auto-run schema migrations
if (!Capsule::schema()->hasTable('users')) {
    Capsule::schema()->create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->string('password');
        $table->string('role')->default('user');
        $table->string('tier')->default('free');
        $table->string('google_id')->nullable();
        $table->timestamps();
    });

    // Seed default admin
    Capsule::table('users')->insert([
        'name' => 'Admin Moderator',
        'email' => 'admin@lgbtqmatrimony.local',
        'password' => password_hash('AdminSecure2026!', PASSWORD_BCRYPT, ['cost' => 12]),
        'role' => 'admin',
        'tier' => 'premium',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);
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

// API Routes
$app->post('/api/v1/auth/register', function (Request $request, Response $response) {
    $data = json_decode($request->getBody()->getContents(), true);
    
    // Validate inputs
    if (empty($data['name']) || empty($data['email']) || empty($data['password']) || empty($data['date_of_birth'])) {
        $response->getBody()->write(json_encode(['success' => false, 'error' => 'Missing required fields.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $response->getBody()->write(json_encode(['success' => false, 'error' => 'Invalid email format.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    // Check if user already exists
    $existing = Capsule::table('users')->where('email', $data['email'])->first();
    if ($existing) {
        $response->getBody()->write(json_encode(['success' => false, 'error' => 'Email address already registered.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    // Create User
    $userId = Capsule::table('users')->insertGetId([
        'name' => $data['name'],
        'email' => $data['email'],
        'password' => password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]),
        'role' => 'user',
        'tier' => 'free',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);

    $user = [
        'id' => $userId,
        'email' => $data['email'],
        'name' => $data['name'],
        'role' => 'user',
        'tier' => 'free'
    ];

    // Emit event for profile service to create empty profile structure
    EventBus::publish('user.registered', array_merge($user, [
        'date_of_birth' => $data['date_of_birth'],
        'gender_identity' => $data['gender_identity'] ?? 'other',
        'gender_custom' => $data['gender_custom'] ?? '',
        'sexual_orientation' => $data['sexual_orientation'] ?? 'other',
        'city' => $data['city'] ?? '',
        'country' => $data['country'] ?? ''
    ]));

    // Issue JWT Token
    $tokens = JWTManager::issue($user);

    $response->getBody()->write(json_encode([
        'success' => true,
        'message' => 'User registered successfully.',
        'user' => $user,
        'tokens' => $tokens
    ]));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
});

$app->post('/api/v1/auth/login', function (Request $request, Response $response) {
    $data = json_decode($request->getBody()->getContents(), true);
    
    if (empty($data['email']) || empty($data['password'])) {
        $response->getBody()->write(json_encode(['success' => false, 'error' => 'Email and password are required.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $dbUser = Capsule::table('users')->where('email', $data['email'])->first();
    
    if (!$dbUser || !password_verify($data['password'], $dbUser->password)) {
        $response->getBody()->write(json_encode(['success' => false, 'error' => 'Invalid email or password credentials.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }

    $user = [
        'id' => $dbUser->id,
        'email' => $dbUser->email,
        'name' => $dbUser->name,
        'role' => $dbUser->role,
        'tier' => $dbUser->tier
    ];

    $tokens = JWTManager::issue($user);

    $response->getBody()->write(json_encode([
        'success' => true,
        'message' => 'Login successful.',
        'user' => $user,
        'tokens' => $tokens
    ]));
    return $response->withHeader('Content-Type', 'application/json');
});

// OAuth Mock (Google)
$app->post('/api/v1/auth/google', function (Request $request, Response $response) {
    $data = json_decode($request->getBody()->getContents(), true);
    
    if (empty($data['token'])) {
        $response->getBody()->write(json_encode(['success' => false, 'error' => 'Google ID token required.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    // Mock resolving Google user details
    $googleId = "g_" . crc32($data['token']);
    $email = "google." . substr(md5($data['token']), 0, 8) . "@gmail.com";
    $name = "Google User " . substr(md5($data['token']), 0, 4);

    $dbUser = Capsule::table('users')->where('google_id', $googleId)->orWhere('email', $email)->first();

    if (!$dbUser) {
        $userId = Capsule::table('users')->insertGetId([
            'name' => $name,
            'email' => $email,
            'password' => password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT, ['cost' => 12]),
            'role' => 'user',
            'tier' => 'free',
            'google_id' => $googleId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        $tier = 'free';
    } else {
        $userId = $dbUser->id;
        $name = $dbUser->name;
        $email = $dbUser->email;
        $tier = $dbUser->tier;
    }

    $user = [
        'id' => $userId,
        'email' => $email,
        'name' => $name,
        'role' => 'user',
        'tier' => $tier
    ];

    // Emit event if new user
    if (!$dbUser) {
        EventBus::publish('user.registered', array_merge($user, [
            'date_of_birth' => '1998-01-01',
            'gender_identity' => 'non-binary',
            'gender_custom' => '',
            'sexual_orientation' => 'queer',
            'city' => 'New York',
            'country' => 'USA'
        ]));
    }

    $tokens = JWTManager::issue($user);

    $response->getBody()->write(json_encode([
        'success' => true,
        'user' => $user,
        'tokens' => $tokens
    ]));
    return $response->withHeader('Content-Type', 'application/json');
});

// Internal subscription update listener (keeps local tier up to date)
// Since Slim is short-running, background workers run the subscriptions listener,
// but we will also expose a sync endpoint or event handling that updates the database.
$app->post('/api/v1/auth/internal/update-tier', function (Request $request, Response $response) {
    $data = json_decode($request->getBody()->getContents(), true);
    
    if (empty($data['user_id']) || empty($data['tier'])) {
        $response->getBody()->write(json_encode(['success' => false, 'error' => 'Missing tier fields.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    Capsule::table('users')->where('id', $data['user_id'])->update([
        'tier' => $data['tier'],
        'updated_at' => date('Y-m-d H:i:s')
    ]);

    $response->getBody()->write(json_encode(['success' => true]));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();
