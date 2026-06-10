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

Database::connect('matrimony_profile');

// Schema migration
if (!Capsule::schema()->hasTable('profiles')) {
    Capsule::schema()->create('profiles', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id')->unique();
        $table->string('name')->nullable();
        $table->string('headline')->nullable();
        $table->text('about_me')->nullable();
        $table->integer('height')->nullable(); // in cm
        $table->string('religion')->nullable();
        $table->string('mother_tongue')->nullable();
        $table->string('education')->nullable();
        $table->string('profession')->nullable();
        $table->string('relationship_intent')->nullable(); // friendship, dating, long-term, marriage
        $table->text('photos')->nullable(); // JSON list
        $table->string('pronouns')->nullable();
        $table->date('date_of_birth')->nullable();
        $table->string('gender_identity')->nullable();
        $table->string('gender_custom')->nullable();
        $table->string('sexual_orientation')->nullable();
        $table->string('city')->nullable();
        $table->string('country')->nullable();
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

// Middleware for token validation
$authMiddleware = new AuthMiddleware();

// Routes
$app->get('/api/v1/profiles/me', function (Request $request, Response $response) {
    $user = $request->getAttribute('user');
    
    $profile = Capsule::table('profiles')->where('user_id', $user['id'])->first();
    
    if (!$profile) {
        $response->getBody()->write(json_encode(['success' => false, 'error' => 'Profile not found.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }

    $response->getBody()->write(json_encode([
        'success' => true,
        'profile' => $profile
    ]));
    return $response->withHeader('Content-Type', 'application/json');
})->add($authMiddleware);

$app->post('/api/v1/profiles/me', function (Request $request, Response $response) {
    $user = $request->getAttribute('user');
    $data = json_decode($request->getBody()->getContents(), true);

    $profileData = [
        'name' => $user['name'],
        'headline' => $data['headline'] ?? null,
        'about_me' => $data['about_me'] ?? null,
        'height' => !empty($data['height']) ? (int)$data['height'] : null,
        'religion' => $data['religion'] ?? null,
        'mother_tongue' => $data['mother_tongue'] ?? null,
        'education' => $data['education'] ?? null,
        'profession' => $data['profession'] ?? null,
        'relationship_intent' => $data['relationship_intent'] ?? null,
        'pronouns' => $data['pronouns'] ?? null,
        'city' => $data['city'] ?? null,
        'country' => $data['country'] ?? null,
        'photos' => isset($data['photos']) ? json_encode($data['photos']) : json_encode([]),
        'updated_at' => date('Y-m-d H:i:s')
    ];

    // Check if birthday / gender was sent
    if (!empty($data['date_of_birth'])) {
        $profileData['date_of_birth'] = $data['date_of_birth'];
    }
    if (!empty($data['gender_identity'])) {
        $profileData['gender_identity'] = $data['gender_identity'];
    }
    if (!empty($data['gender_custom'])) {
        $profileData['gender_custom'] = $data['gender_custom'];
    }
    if (!empty($data['sexual_orientation'])) {
        $profileData['sexual_orientation'] = $data['sexual_orientation'];
    }

    $exists = Capsule::table('profiles')->where('user_id', $user['id'])->exists();
    if ($exists) {
        Capsule::table('profiles')->where('user_id', $user['id'])->update($profileData);
    } else {
        $profileData['user_id'] = $user['id'];
        $profileData['created_at'] = date('Y-m-d H:i:s');
        Capsule::table('profiles')->insert($profileData);
    }

    // Load fresh profile
    $profile = Capsule::table('profiles')->where('user_id', $user['id'])->first();

    // Broadcast updated profile to event bus so discovery-service can keep in sync
    EventBus::publish('profile.updated', (array)$profile);

    $response->getBody()->write(json_encode([
        'success' => true,
        'message' => 'Profile updated successfully.',
        'profile' => $profile
    ]));
    return $response->withHeader('Content-Type', 'application/json');
})->add($authMiddleware);

// Retrieve details for a target profile
$app->get('/api/v1/profiles/{id}', function (Request $request, Response $response, array $args) {
    $viewer = $request->getAttribute('user');
    $targetId = (int)$args['id'];

    $profile = Capsule::table('profiles')->where('user_id', $targetId)->first();
    if (!$profile) {
        $response->getBody()->write(json_encode(['success' => false, 'error' => 'Profile not found.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }

    $profileArray = (array)$profile;

    // Trigger profile view in activity-service by publishing an event
    if ($viewer['id'] !== $targetId) {
        EventBus::publish('profile.viewed', [
            'viewer_id' => $viewer['id'],
            'target_id' => $targetId,
            'timestamp' => time()
        ]);
    }

    // Access control: check subscription tier
    if ($viewer['tier'] !== 'premium' && $viewer['id'] !== $targetId) {
        // Blur and hide sensitive details for free tier
        $profileArray['headline'] = "[Go Premium to View]";
        $profileArray['about_me'] = "[Go Premium to View]";
        $profileArray['contact_hidden'] = true;
        // Blur name / details slightly or send standard free format
        $profileArray['name'] = substr($profileArray['name'], 0, 1) . "...";
        $profileArray['email'] = "premium-locked@lgbtqmatrimony.local";
        $profileArray['phone'] = "Locked";
    } else {
        $profileArray['contact_hidden'] = false;
        // Mock email matching
        $profileArray['email'] = "user" . $targetId . "@lgbtqmatrimony.local";
        $profileArray['phone'] = "+1 (555) 019-928" . ($targetId % 10);
    }

    $response->getBody()->write(json_encode([
        'success' => true,
        'profile' => $profileArray
    ]));
    return $response->withHeader('Content-Type', 'application/json');
})->add($authMiddleware);

// Endpoint for internal service calls to query profile info
$app->get('/api/v1/profiles/internal/{id}', function (Request $request, Response $response, array $args) {
    $targetId = (int)$args['id'];
    $profile = Capsule::table('profiles')->where('user_id', $targetId)->first();
    
    if (!$profile) {
        $response->getBody()->write(json_encode(['success' => false, 'error' => 'Profile not found.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }

    $response->getBody()->write(json_encode([
        'success' => true,
        'profile' => $profile
    ]));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();
