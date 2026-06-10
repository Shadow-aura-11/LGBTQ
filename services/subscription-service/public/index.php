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

Database::connect('matrimony_subscription');

// Schema migration for subscription tracking
if (!Capsule::schema()->hasTable('subscriptions')) {
    Capsule::schema()->create('subscriptions', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->string('plan_type'); // monthly, annual
        $table->string('status'); // active, expired
        $table->string('gateway'); // stripe, razorpay
        $table->string('payment_id')->nullable();
        $table->timestamp('expires_at')->nullable();
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

// Get Current User Subscription
$app->get('/api/v1/subscriptions/status', function (Request $request, Response $response) {
    $user = $request->getAttribute('user');
    
    $sub = Capsule::table('subscriptions')
        ->where('user_id', $user['id'])
        ->where('status', 'active')
        ->where('expires_at', '>', date('Y-m-d H:i:s'))
        ->orderBy('id', 'desc')
        ->first();

    $response->getBody()->write(json_encode([
        'success' => true,
        'subscription' => $sub,
        'tier' => $sub ? 'premium' : 'free'
    ]));
    return $response->withHeader('Content-Type', 'application/json');
})->add($authMiddleware);

// Initialize Checkout (Stripe & Razorpay)
$app->post('/api/v1/subscriptions/checkout', function (Request $request, Response $response) {
    $user = $request->getAttribute('user');
    $data = json_decode($request->getBody()->getContents(), true);

    $plan = $data['plan'] ?? 'monthly'; // monthly, annual
    $gateway = $data['gateway'] ?? 'stripe'; // stripe, razorpay

    if (!in_array($plan, ['monthly', 'annual']) || !in_array($gateway, ['stripe', 'razorpay'])) {
        $response->getBody()->write(json_encode(['success' => false, 'error' => 'Invalid plan or gateway selected.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $amount = ($plan === 'monthly') ? 999 : 7999; // $9.99 or $79.99 (in cents/paise)
    $currency = ($gateway === 'razorpay') ? 'INR' : 'USD';

    // Mocking Checkout Creation Response
    $paymentId = "pay_" . bin2hex(random_bytes(10));
    $checkoutUrl = "http://localhost:4111/subscription/mock-payment?pay_id={$paymentId}&amount={$amount}&currency={$currency}&gateway={$gateway}&plan={$plan}&user_id={$user['id']}";

    $response->getBody()->write(json_encode([
        'success' => true,
        'checkout_url' => $checkoutUrl,
        'payment_id' => $paymentId,
        'amount' => $amount,
        'currency' => $currency,
        'gateway' => $gateway,
        'plan' => $plan
    ]));
    return $response->withHeader('Content-Type', 'application/json');
})->add($authMiddleware);

// Stripe & Razorpay Webhook Handlers
$app->post('/api/v1/subscriptions/webhook', function (Request $request, Response $response) {
    $data = json_decode($request->getBody()->getContents(), true);

    // Validate webhook payload (Mock confirmation check)
    if (empty($data['payment_id']) || empty($data['user_id']) || empty($data['plan'])) {
        $response->getBody()->write(json_encode(['success' => false, 'error' => 'Invalid webhook payload.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $userId = (int)$data['user_id'];
    $plan = $data['plan'];
    $gateway = $data['gateway'] ?? 'stripe';
    $paymentId = $data['payment_id'];

    $duration = ($plan === 'monthly') ? '+30 days' : '+365 days';
    $expiresAt = date('Y-m-d H:i:s', strtotime($duration));

    // Deactivate previous active subscriptions
    Capsule::table('subscriptions')
        ->where('user_id', $userId)
        ->update(['status' => 'expired']);

    // Record active subscription
    Capsule::table('subscriptions')->insert([
        'user_id' => $userId,
        'plan_type' => $plan,
        'status' => 'active',
        'gateway' => $gateway,
        'payment_id' => $paymentId,
        'expires_at' => $expiresAt,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);

    // Perform internal REST call to auth-service to update user's tier immediately
    $client = new \GuzzleHttp\Client(['fallback' => true]); // fallback mock
    // Emit Event for Redis Bus so other microservices (auth, chat, discovery) know user is premium
    EventBus::publish('subscription.activated', [
        'user_id' => $userId,
        'tier' => 'premium',
        'expires_at' => $expiresAt
    ]);

    $response->getBody()->write(json_encode(['success' => true, 'message' => 'Subscription activated successfully.']));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();
