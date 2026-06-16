<?php

session_start();

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

// Simple Helper to make HTTP requests to microservices through Nginx
function makeApiRequest($method, $path, $data = [], $token = null) {
    $url = "http://nginx" . $path; // Call through Nginx inside Docker Network
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    $headers = [
        'Content-Type: application/json'
    ];
    
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    if (!empty($data)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'status' => $httpCode,
        'data' => json_decode($response, true) ?: $response
    ];
}

// Extract token from cookie
$token = $_COOKIE['jwt_token'] ?? null;
$currentUser = null;

if ($token) {
    // Decode user info from JWT manually or query auth-service
    // For local performance, decode token payload (JWT payload is Base64)
    $parts = explode('.', $token);
    if (count($parts) === 3) {
        $payload = json_decode(base64_decode($parts[1]), true);
        $currentUser = $payload['user'] ?? null;
    }
}

// Fetch unread notifications check for navbar indicator
$hasUnreadNotifs = false;
if ($currentUser && $token) {
    $notifCheck = makeApiRequest('GET', '/api/v1/notifications', [], $token);
    if ($notifCheck['status'] === 200 && isset($notifCheck['data']['notifications'])) {
        foreach ($notifCheck['data']['notifications'] as $notif) {
            if (empty($notif['is_read'])) {
                $hasUnreadNotifs = true;
                break;
            }
        }
    }
}
$notifDotClass = $hasUnreadNotifs ? '' : 'hidden';

// Views loader route resolver
switch ($uri) {
    case '/':
    case '/index.php':
        require __DIR__ . '/../views/landing.php';
        break;
    case '/login':
        require __DIR__ . '/../views/login.php';
        break;
    case '/register':
        require __DIR__ . '/../views/register.php';
        break;
    case '/dashboard':
        require __DIR__ . '/../views/dashboard.php';
        break;
    case '/discovery':
        require __DIR__ . '/../views/browse.php';
        break;
    case '/profile/setup':
        require __DIR__ . '/../views/profile_setup.php';
        break;
    case '/profile':
        require __DIR__ . '/../views/profile_view.php';
        break;
    case '/subscription':
        require __DIR__ . '/../views/subscription.php';
        break;
    case '/subscription/mock-payment':
        require __DIR__ . '/../views/mock_payment.php';
        break;
    case '/chat':
        require __DIR__ . '/../views/chat.php';
        break;
    case '/notifications':
        require __DIR__ . '/../views/notifications.php';
        break;
    case '/settings':
        require __DIR__ . '/../views/settings.php';
        break;
    case '/admin':
        require __DIR__ . '/../views/admin.php';
        break;
    case '/logout':
        setcookie('jwt_token', '', time() - 3600, '/');
        header('Location: /');
        exit;
    default:
        // Handle custom profile viewing /profile/123
        if (preg_match('#^/profile/([0-9]+)$#', $uri, $matches)) {
            $viewTargetId = (int)$matches[1];
            require __DIR__ . '/../views/profile_view.php';
        } elseif (preg_match('#^/(page|blog)/([a-zA-Z0-9-]+)$#', $uri, $matches)) {
            $contentType = $matches[1]; // page or blog
            $slug = $matches[2];
            $cmsFile = __DIR__ . '/../../scratch/cms.json';
            $cmsItem = null;
            if (file_exists($cmsFile)) {
                $cmsData = json_decode(file_get_contents($cmsFile), true) ?: [];
                foreach ($cmsData as $item) {
                    // Normalize type (blog posts are stored as 'post', but routing is /blog/...)
                    $itemType = ($item['type'] === 'post') ? 'blog' : 'page';
                    if ($itemType === $contentType && $item['slug'] === $slug) {
                        $cmsItem = $item;
                        break;
                    }
                }
            }
            if ($cmsItem) {
                require __DIR__ . '/../views/cms_view.php';
            } else {
                http_response_code(404);
                echo "Page not found";
            }
        } else {
            http_response_code(404);
            echo "Page not found";
        }
        break;
}
