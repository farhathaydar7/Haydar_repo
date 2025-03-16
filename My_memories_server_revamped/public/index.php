<?php
require_once __DIR__.'/../vendor/autoload.php';

use MyApp\Routes\ApiRoutes;
use MyApp\Services\JwtService;
use MyApp\Services\ImageService;
use MyApp\Models\{PhotoModel, UserModel, TagModel};
use MyApp\Utils\Database;
use MyApp\Controllers\{PhotoController, AuthController};
use MyApp\Middleware\{JwtMiddleware, CorsMiddleware};

// Load config
$config = require __DIR__.'/../config/config.php';

// Initialize middleware FIRST
$corsMiddleware = new CorsMiddleware();

// Initialize dependencies IN CORRECT ORDER
$db = Database::getInstance($config['database']);
$jwtService = new JwtService($config['jwt']['secret'], $config['jwt']['expiry']);

$jwtMiddleware = new JwtMiddleware($jwtService); // Now has access to initialized JwtService

// Initialize database connection
$db = Database::getInstance($config['database']);

// Models (depend on DB)
$userModel = new UserModel($db, $config['jwt']['secret']);
$photoModel = new PhotoModel($db);
$tagModel = new TagModel($db);

// Services
$imageService = new ImageService(
    $config['uploads']['dir'],
    $config['uploads']['public_path']
);

// Controllers
$authController = new AuthController(
    $userModel,
    $jwtService
);

$photoController = new PhotoController(
    $photoModel,
    $imageService,
    $jwtMiddleware, // Now properly initialized
    $tagModel
);

// Handle request
(new ApiRoutes(
    $photoController,
    $authController,
    $corsMiddleware // Use pre-initialized middleware
))->handle(
    $_SERVER['REQUEST_METHOD'],
    rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/', '/') ?: '/'
);