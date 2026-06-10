<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Shared\Database;
use Shared\Middleware\AuthMiddleware;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as Capsule;

require __DIR__ . '/../../../vendor/autoload';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../../');
$dotenv->safeLoad();

Database::connect('matrimony_discovery');

// Schema migration for discovery profiles (sync repository for read performance)
if (!Capsule::schema()->hasTable('discovery_profiles')) {
    Capsule::schema()->create('discovery_profiles', function (Blueprint $table) {
        $table->unsignedBigInteger('user_id')->primary();
        $table->string('name')->nullable();
        $table->string('headline')->nullable();
        $table->string('pronouns')->nullable();
        $table->date('date_of_birth')->nullable();
        $table->string('gender_identity')->nullable();
        $table->string('gender_custom')->nullable();
        $table->string('sexual_orientation')->nullable();
        $table->string('city')->nullable();
        $table->string('country')->nullable();
        $table->string('relationship_intent')->nullable();
        $table->text('photos')->nullable();
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

// Match Feed Endpoint
$app->get('/api/v1/discovery/feed', function (Request $request, Response $response) {
    $user = $request->getAttribute('user');
    $params = $request->getQueryParams();

    // Start with all other profiles
    $query = Capsule::table('discovery_profiles')->where('user_id', '!=', $user['id']);

    // Premium gets advanced filters, free has basic orientation filters
    $gender = $params['gender_identity'] ?? null;
    $orientation = $params['sexual_orientation'] ?? null;
    $city = $params['city'] ?? null;
    $intent = $params['relationship_intent'] ?? null;
    $minAge = isset($params['min_age']) ? (int)$params['min_age'] : 18;
    $maxAge = isset($params['max_age']) ? (int)$params['max_age'] : 99;

    // Filter by Age
    if ($minAge || $maxAge) {
        $maxDob = date('Y-m-d', strtotime("-$minAge years"));
        $minDob = date('Y-m-d', strtotime("-$maxAge years"));
        $query->whereBetween('date_of_birth', [$minDob, $maxDob]);
    }

    // Filter by Gender Identity
    if ($gender) {
        $query->where('gender_identity', $gender);
    }

    // Filter by Sexual Orientation
    if ($orientation) {
        $query->where('sexual_orientation', $orientation);
    }

    // Advanced filters restricted to Premium
    if ($user['tier'] === 'premium') {
        if ($city) {
            $query->where('city', 'like', "%$city%");
        }
        if ($intent) {
            $query->where('relationship_intent', $intent);
        }
    }

    $profiles = $query->limit(20)->get()->map(function ($profile) use ($user) {
        $profileArray = (array)$profile;
        if ($user['tier'] !== 'premium') {
            // Hide specific fields if viewing as a free user
            $profileArray['headline'] = "[Go Premium to View]";
            $profileArray['name'] = substr($profileArray['name'], 0, 1) . "...";
        }
        return $profileArray;
    });

    $response->getBody()->write(json_encode([
        'success' => true,
        'feed' => $profiles,
        'filters_applied' => [
            'min_age' => $minAge,
            'max_age' => $maxAge,
            'gender_identity' => $gender,
            'sexual_orientation' => $orientation,
            'city_restricted' => $user['tier'] !== 'premium' && $city ? 'Requires Premium' : $city,
            'intent_restricted' => $user['tier'] !== 'premium' && $intent ? 'Requires Premium' : $intent
        ]
    ]));
    return $response->withHeader('Content-Type', 'application/json');
})->add($authMiddleware);

// Internal Synchronizer endpoint (receives user profile changes)
$app->post('/api/v1/discovery/internal/sync', function (Request $request, Response $response) {
    $data = json_decode($request->getBody()->getContents(), true);

    if (empty($data['user_id'])) {
        $response->getBody()->write(json_encode(['success' => false, 'error' => 'Missing user_id.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $profileData = [
        'name' => $data['name'] ?? null,
        'headline' => $data['headline'] ?? null,
        'pronouns' => $data['pronouns'] ?? null,
        'date_of_birth' => $data['date_of_birth'] ?? null,
        'gender_identity' => $data['gender_identity'] ?? null,
        'gender_custom' => $data['gender_custom'] ?? null,
        'sexual_orientation' => $data['sexual_orientation'] ?? null,
        'city' => $data['city'] ?? null,
        'country' => $data['country'] ?? null,
        'relationship_intent' => $data['relationship_intent'] ?? null,
        'photos' => $data['photos'] ?? null,
        'updated_at' => date('Y-m-d H:i:s')
    ];

    $exists = Capsule::table('discovery_profiles')->where('user_id', $data['user_id'])->exists();
    if ($exists) {
        Capsule::table('discovery_profiles')->where('user_id', $data['user_id'])->update($profileData);
    } else {
        $profileData['user_id'] = $data['user_id'];
        $profileData['created_at'] = date('Y-m-d H:i:s');
        Capsule::table('discovery_profiles')->insert($profileData);
    }

    $response->getBody()->write(json_encode(['success' => true, 'message' => 'Discovery indexed.']));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();
