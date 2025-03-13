<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__.'/../config.php';
require_once __DIR__.'/../models/Photo.Model.php';
require_once __DIR__.'/../vendor/autoload.php'; // Include JWT library
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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
    
    // Process the request body first to get potential owner_id directly from frontend
    $request_body = file_get_contents('php://input');
    $data = json_decode($request_body);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON input: " . json_last_error_msg(), 400);
    }
    
    // Try to get owner_id from request payload first
    $owner_id = null;
    if (isset($data->owner_id) && !empty($data->owner_id)) {
        $owner_id = $data->owner_id;
    } else {
        // Decode the JWT token as fallback
        global $jwt_secret; // Ensure this is defined in your config.php
        try {
            $decoded = JWT::decode($jwt, new Key($jwt_secret, 'HS256'));
            // Extract the user ID from the decoded token
            if (isset($decoded->user_id)) {
                $owner_id = $decoded->user_id;
            } else if (isset($decoded->id)) {
                $owner_id = $decoded->id;
            } else if (isset($decoded->sub)) {
                $owner_id = $decoded->sub;
            } else {
                throw new Exception('User ID not found in token', 401);
            }
        } catch (Exception $e) {
            throw new Exception('Invalid or expired token: ' . $e->getMessage(), 401);
        }
    }
    
    if (!$owner_id) {
        throw new Exception('User ID not available', 401);
    }
    
    if (!isset($data->image) || empty($data->image)) {
        throw new InvalidArgumentException('No image data provided', 400);
    }
    
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
    
    // Retrieve additional data from the JSON payload
    $title = isset($data->title) ? $data->title : 'Untitled';
    $date = isset($data->date) ? $data->date : date('Y-m-d');
    $description = isset($data->description) ? $data->description : '';
    $tag_name = isset($data->tag) ? $data->tag : null;
    
    // Find or create tag
    $tag_id = null;
    if (!empty($tag_name)) {
        require_once __DIR__.'/../models/Tag.Model.php';
        $tagModel = new TagModel();
        $tag_id = $tagModel->findOrCreateTag($tag_name, $owner_id);
    }
    
    // Create memory entry
    $photoModel = new PhotoModel();
    $memoryCreated = $photoModel->create(
        $owner_id,
        $title,
        $date,
        $description,
        $tag_id,
        $imageData
    );
    
    if ($memoryCreated) {
        echo json_encode([
            'success' => true,
            'message' => 'Image uploaded successfully',
            'filePath' => $memoryCreated
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to save memory info to database'
        ]);
    }

} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>