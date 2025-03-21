<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
require_once __DIR__.'/../config.php';
require_once __DIR__.'/../models/User.Model.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed', 405);
    }

    $data = json_decode(file_get_contents('php://input'), true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new InvalidArgumentException('Invalid JSON', 400);
    }

    $required = ['username', 'email', 'password'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new InvalidArgumentException("Missing field: $field", 400);
        }
    }

    $userModel = new UserModel();
    $userId = $userModel->create($data);

    echo json_encode([
        'success' => true,
        'user_id' => $userId
    ]);

} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
} catch (RuntimeException $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode(['error' => $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}