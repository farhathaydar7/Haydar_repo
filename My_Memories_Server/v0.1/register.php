<?php
require_once __DIR__.'/../config.php';
require_once __DIR__.'/../models/User.Model.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed', 405);
    }

    $data = json_decode(file_get_contents('php://input'), true);
    
    $userModel = new UserModel();
    $userId = $userModel->create([
        'email' => $data['email'] ?? '',
        'password' => $data['password'] ?? ''
    ]);

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'user_id' => $userId
    ]);

} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
} catch (RuntimeException $e) {
    http_response_code(409);
    echo json_encode(['error' => $e->getMessage()]);
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode(['error' => $e->getMessage()]);
}