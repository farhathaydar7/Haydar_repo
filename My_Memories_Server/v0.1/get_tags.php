<?php
require_once __DIR__ . '/../vendor/autoload.php'; // Include Composer autoloader
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/Tag.Model.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception('Method not allowed', 405);
    }

    // Check for JWT token in the Authorization header
    $headers = getallheaders();
    if (!isset($headers['Authorization'])) {
        throw new Exception('Authorization header required', 401);
    }

    $jwt = str_replace('Bearer ', '', $headers['Authorization']);
    global $jwt_secret;
    
    // Decode JWT token
    $decoded = JWT::decode($jwt, new Key($jwt_secret, 'HS256'));
    $user_id = $decoded->user_id ?? $decoded->id ?? $decoded->sub ?? null;

    if (!$user_id) {
        throw new Exception('Invalid token', 401);
    }

    // Fetch tags
    $tagModel = new TagModel();
    $tags = $tagModel->getTagsByOwner($user_id);

    echo json_encode([
        'success' => true,
        'tags' => $tags
    ]);

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>