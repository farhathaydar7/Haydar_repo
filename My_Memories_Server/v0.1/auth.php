<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");


// backend/api/index.php

require_once __DIR__ . '../controllers/Gallery.Controller.php';
require_once __DIR__ . '../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header("Content-Type: application/json");
header('Access-Control-Allow-Origin: *');

$controller = new GalleryController();

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = str_replace('/api', '', $uri);

// Public route for login
if ($uri === '/login' && $method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!empty($data['username']) && !empty($data['password'])) {
        $result = $controller->login($data['username'], $data['password']);
        if ($result) {
            echo json_encode(['success' => true, 'token' => $result['token']]);
        } else {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Username and password required']);
    }
    exit;
}

// JWT Middleware for protected routes
$headers = getallheaders();
if (!isset($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authorization header not found']);
    exit;
}
$authHeader = $headers['Authorization'];
list($jwt) = sscanf($authHeader, 'Bearer %s');
if (!$jwt) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Token not provided']);
    exit;
}

global $jwt_secret;
try {
    $decoded = JWT::decode($jwt, new Key($jwt_secret, 'HS256'));
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid token']);
    exit;
}

http_response_code(404);
echo json_encode(['success' => false, 'message' => 'Route not found']);
?>
