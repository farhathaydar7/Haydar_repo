<?php
require_once __DIR__.'/../vendor/autoload.php';

use MyApp\Routes\ApiRoutes;
use MyApp\Services\JwtService;
use MyApp\Services\ImageService;
use MyApp\Models\{PhotoModel, UserModel};
use MyApp\Utils\Database;
use MyApp\Controllers\{PhotoController, AuthController};
use MyApp\Middleware\{JwtMiddleware, CorsMiddleware};

// Load config
$config = require __DIR__.'/../config/config.php';

// Initialize dependencies
$db = Database::getInstance($config['database']);
$jwtService = new JwtService($config['jwt']['secret'], $config['jwt']['expiry']);

// Models
$userModel = new UserModel($db, $config['jwt']['secret']);
$photoModel = new PhotoModel($db);

// Services
$imageService = new ImageService($config['uploads']['dir']);

// Controllers
$authController = new AuthController(
    $userModel,
    $jwtService
);
$photoController = new PhotoController(
    $photoModel,
    $imageService,
    new JwtMiddleware($jwtService)
);

// Handle request
(new ApiRoutes(
    $photoController,
    $authController,
    new CorsMiddleware()
))
    ->handle($_SERVER['REQUEST_METHOD'],
        rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/', '/') ?: '/'
    );