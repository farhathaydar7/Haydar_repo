<?php
require_once __DIR__.'/../config.php';
require_once __DIR__.'/../models/Photo.Model.php';
require_once __DIR__.'/../vendor/autoload.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception('Method not allowed', 405);
    }

    // Authentication
    $headers = getallheaders();
    if (!isset($headers['Authorization'])) {
        throw new Exception('Authorization header required', 401);
    }

    $jwt = str_replace('Bearer ', '', $headers['Authorization']);
    global $jwt_secret;
    $decoded = JWT::decode($jwt, new Key($jwt_secret, 'HS256'));
    $user_id = $decoded->user_id ?? $decoded->id ?? $decoded->sub ?? null;

    if (!$user_id) {
        throw new Exception('Invalid token', 401);
    }

    if (!isset($_GET['photo_id'])) {
        throw new Exception('Photo ID required', 400);
    }

    $photoModel = new PhotoModel();
    $photo = $photoModel->getPhotoById($_GET['photo_id']);

    if (!$photo || $photo['owner_id'] != $user_id) {
        throw new Exception('Photo not found', 404);
    }

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