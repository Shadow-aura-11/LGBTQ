<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Shared\Middleware\AuthMiddleware;

require __DIR__ . '/../../../vendor/autoload';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../../');
$dotenv->safeLoad();

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

// Handle Image Upload
$app->post('/api/v1/media/upload', function (Request $request, Response $response) {
    $user = $request->getAttribute('user');
    $uploadedFiles = $request->getUploadedFiles();

    if (empty($uploadedFiles['photo'])) {
        $response->getBody()->write(json_encode(['success' => false, 'error' => 'No image file uploaded.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $uploadedFile = $uploadedFiles['photo'];
    
    // Validate file errors
    if ($uploadedFile->getError() !== UPLOAD_ERR_OK) {
        $response->getBody()->write(json_encode(['success' => false, 'error' => 'File upload error occurred.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    // Validate size (max 5 MB)
    if ($uploadedFile->getSize() > 5 * 1024 * 1024) {
        $response->getBody()->write(json_encode(['success' => false, 'error' => 'File size exceeds 5MB limit.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    // Validate MIME type
    $mediaType = $uploadedFile->getClientMediaType();
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    
    if (!in_array($mediaType, $allowedTypes)) {
        $response->getBody()->write(json_encode(['success' => false, 'error' => 'Invalid file format. Only JPEG, PNG, and WebP are allowed.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    // Create target directory if it doesn't exist
    $targetDir = __DIR__ . '/../../../uploads';
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION) ?: 'jpg';
    $filename = sprintf('photo_%d_%s.%s', $user['id'], bin2hex(random_bytes(8)), $extension);
    $targetPath = $targetDir . '/' . $filename;

    // Save uploaded file
    $uploadedFile->moveTo($targetPath);

    // Apply basic image compression if GD is available
    if (extension_loaded('gd')) {
        try {
            if ($mediaType === 'image/jpeg') {
                $img = imagecreatefromjpeg($targetPath);
                if ($img) {
                    imagejpeg($img, $targetPath, 75); // 75% quality compression
                    imagedestroy($img);
                }
            } elseif ($mediaType === 'image/png') {
                $img = imagecreatefrompng($targetPath);
                if ($img) {
                    imagepng($img, $targetPath, 6); // level 6 compression (0-9)
                    imagedestroy($img);
                }
            }
        } catch (\Exception $e) {
            // Log warning or skip compression if file error
        }
    }

    $publicUrl = '/uploads/' . $filename;

    $response->getBody()->write(json_encode([
        'success' => true,
        'url' => $publicUrl,
        'message' => 'Image uploaded and compressed successfully.'
    ]));
    return $response->withHeader('Content-Type', 'application/json');
})->add($authMiddleware);

$app->run();
