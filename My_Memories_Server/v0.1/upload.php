<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__.'/../config.php';
require_once __DIR__.'/../models/Photo.Model.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed', 405);
    }

    // Ensure user is logged in (check for Authorization header and JWT) - you might want to implement JWT verification here as in auth.php

    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        throw new InvalidArgumentException('No image uploaded or upload error', 400);
    }

    $owner_id = 1; // Replace with actual user ID from JWT if you implement user authentication
    $title = $_POST['title'] ?? 'Untitled';
    $date = $_POST['date'] ?? date('Y-m-d'); // Default to today's date
    $description = $_POST['description'] ?? '';
    $tag_id = $_POST['tag_id'] ?? null; // Or 0, depending on your needs

    $photoModel = new PhotoModel();
    $image_url = $photoModel->uploadImage($_FILES['image'], $owner_id, uniqid('img_', true)); // Using uniqid for image_id

    if ($image_url) {
        // After successful upload, you might want to create a database entry as well, similar to PhotoModel::create, but without re-uploading
        $memoryCreated = $photoModel->create($owner_id, $title, $date, $description, $tag_id, $_FILES['image']);
        if($memoryCreated){
            http_response_code(201);
            echo json_encode(['success' => true, 'message' => 'Image uploaded successfully', 'image_url' => $image_url]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Image uploaded to server but failed to save memory info to database']);
        }

    } else {
        throw new RuntimeException('Image upload failed', 500);
    }

} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
} catch (RuntimeException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>