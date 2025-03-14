<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__.'/../config.php';
require_once __DIR__.'/../models/Photo.Model.php';
require_once __DIR__.'/../models/Tag.Model.php';
require_once __DIR__.'/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
        throw new Exception('Method not allowed', 405);
    }

    // Check for JWT token in the Authorization header
    $headers = getallheaders();
    if (!isset($headers['Authorization'])) {
        throw new Exception('Authorization header not found', 401);
    }

    $authHeader = $headers['Authorization'];
    list($jwt) = sscanf($authHeader, 'Bearer %s');
    if (!$jwt) {
        throw new Exception('Token not provided', 401);
    }

    // Decode the JWT token
    global $jwt_secret;
    try {
        $decoded = JWT::decode($jwt, new Key($jwt_secret, 'HS256'));
        $user_id = $decoded->user_id ?? $decoded->id ?? $decoded->sub ?? null;
        if (!$user_id) {
            throw new Exception('User ID not found in token', 401);
        }
    } catch (Exception $e) {
        throw new Exception('Invalid or expired token: ' . $e->getMessage(), 401);
    }

    // Get the request body
    $request_body = file_get_contents('php://input');
    $data = json_decode($request_body, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON input: " . json_last_error_msg(), 400);
    }

    // Validate required fields
    if (!isset($data['photo_id'])) {
        throw new Exception('Photo ID required', 400);
    }

    // Handle tag creation or retrieval
    $tag_id = null;
    if (!empty($data['tag'])) {
        $tagModel = new TagModel();
        $tag_id = $tagModel->findOrCreateTag($data['tag'], $user_id);
    }

    // Update photo data
    $photoModel = new PhotoModel();
    $updateData = [
        'title' => $data['title'] ?? null,
        'date' => $data['date'] ?? null,
        'description' => $data['description'] ?? null,
        'tag_id' => $tag_id, // Use the tag ID
    ];

    if (isset($data['image'])) {
        $updateData['image'] = $data['image'];
    }

    $updatedPhoto = $photoModel->updatePhoto($data['photo_id'], $user_id, $updateData);

    if ($updatedPhoto) {
        echo json_encode([
            'success' => true,
            'message' => 'Photo updated successfully',
            'data' => $updatedPhoto
        ]);
    } else {
        throw new Exception('Failed to update photo', 500);
    }

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>