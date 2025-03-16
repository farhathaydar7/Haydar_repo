<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/Photo.Model.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Only allow GET requests
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

    // Validate photo_id parameter
    if (!isset($_GET['photo_id'])) {
        throw new Exception('Photo ID required', 400);
    }

    $photo_id = $_GET['photo_id'];
    if (!is_numeric($photo_id)) {
        throw new Exception('Invalid photo ID', 400);
    }

    // Fetch photo details
    $photoModel = new PhotoModel();
    $photo = $photoModel->getPhotoById($photo_id);

    if (!$photo || $photo['owner_id'] != $user_id) {
        throw new Exception('Photo not found', 404);
    }

    // Return photo details
    echo json_encode([
        'success' => true,
        'data' => [
            'id' => $photo['id'],
            'title' => $photo['title'],
            'date' => $photo['date'],
            'description' => $photo['description'],
            'image_url' => $photo['image_url'],
            'tag_name' => $photo['tag_name']
        ]
    ]);

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>