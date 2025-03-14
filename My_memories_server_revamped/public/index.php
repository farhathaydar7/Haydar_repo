<?php
require_once __DIR__ . '/../vendor/autoload.php';

use MyApp\Controllers\AuthController;
use MyApp\Middleware\JwtMiddleware;
use MyApp\Models\UserModel;
use MyApp\Services\JwtService;
use MyApp\Utils\Database;

// Load config
$config = require __DIR__ . '/../config/config.php';

// Initialize dependencies
$db = Database::getInstance($config['database']);
$userModel = new UserModel($db, $config['jwt']['secret']);
$jwtService = new JwtService($config['jwt']['secret'], $config['jwt']['expiry']);
$authController = new AuthController($userModel, $jwtService);
$jwtMiddleware = new JwtMiddleware($jwtService);

// Handle CORS and preflight
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit();

// Route handling
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($uri === '/register' && $method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid JSON', 400);
        }

        $response = $authController->register($data);
        echo json_encode($response);
        exit();
    }

    if ($uri === '/login' && $method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $response = $authController->login($data['email'], $data['password']);
        echo json_encode($response);
        exit();
    }

    // Protected routes
    $decoded = $jwtMiddleware->handle();

    if ($uri === '/protected' && $method === 'GET') {
        echo json_encode(['success' => true, 'user' => $decoded]);
        exit();
    }

    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Route not found']);
} catch (\MyApp\Exceptions\UserAlreadyExistsException $e) {
    http_response_code(409);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch (\MyApp\Exceptions\ValidationException $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch (\MyApp\Exceptions\AuthenticationException $e) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch (\InvalidArgumentException $e) {
    http_response_code($e->getCode());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>