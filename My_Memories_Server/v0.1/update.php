<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__.'/../config.php';
require_once __DIR__.'/../models/Photo.Model.php';
require_once __DIR__.'/../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
        throw new Exception('Method not allowed', 405);
    }

    // Validate JWT
    $headers = getallheaders();
    if (!isset($headers['Authorization'])) {
        throw new Exception('Authorization header not found', 401);
    }

    $authHeader = $headers['Authorization'];
    list($jwt) = sscanf($authHeader, 'Bearer %s');
    if (!$jwt) {
        throw new Exception('Token not provided', 401);
    }

    // Decode JWT
    global $jwt_secret;
    try {
        $decoded = JWT::decode($jwt, new Key($jwt_secret, 'HS256'));
        $user_id = $decoded->user_id ?? $decoded->id ?? $decoded->sub ?? null;
        if (!$user_id) throw new Exception('User ID not found in token', 401);
    } catch (Exception $e) {
        throw new Exception('Invalid token: ' . $e->getMessage(), 401);
    }

    // Get request data
    $data = json_decode(file_get_contents('php://input'));
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON: " . json_last_error_msg(), 400);
    }

    // Validate required fields
    if (!isset($data->photo_id) || empty($data->photo_id)) {
        throw new Exception('Photo ID required', 400);
    }

    $photoModel = new PhotoModel();
    $existingPhoto = $photoModel->getPhotoById($data->photo_id);
    
    // Verify photo exists and ownership
    if (!$existingPhoto || $existingPhoto['owner_id'] != $user_id) {
        throw new Exception('Photo not found or unauthorized', 404);
    }

    // Process image if provided
    $imageData = null;
    if (isset($data->image)) {
        $base64Image = $data->image;
        $imageData = base64_decode($base64Image);
        if ($imageData === false) {
            throw new InvalidArgumentException('Invalid base64 image data', 400);
        }

        // Validate MIME type
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($imageData);
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($mimeType, $allowedTypes)) {
            throw new InvalidArgumentException('Invalid image type', 400);
        }
    }

    // Process tags
    $tag_id = $existingPhoto['tag_id'];
    if (isset($data->tag)) {
        require_once __DIR__.'/../models/Tag.Model.php';
        $tagModel = new TagModel();
        
        if (empty($data->tag)) {
            $tag_id = null;
        } else {
            $tag_id = $tagModel->findOrCreateTag($data->tag, $user_id);
        }
    }

    // Prepare update fields
    $updateData = [
        'title' => $data->title ?? null,
        'date' => $data->date ?? null,
        'description' => $data->description ?? null,
        'tag_id' => $tag_id,
        'image' => $imageData
    ];

    // Perform update
    $updatedPhoto = $photoModel->updatePhoto(
        $data->photo_id,
        $user_id,
        $updateData
    );

    if ($updatedPhoto) {
        echo json_encode([
            'success' => true,
            'message' => 'Photo updated successfully',
            'photo' => $updatedPhoto
        ]);
    } else {
        throw new Exception('Failed to update photo', 500);
    }

} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
} catch (Exception $e) {
    $code = $e->getCode() ?: 500;
    http_response_code($code);
    echo json_encode(['error' => $e->getMessage()]);
}