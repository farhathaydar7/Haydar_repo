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
    
    $authResult = $userModel->authenticate(
        $data['email'] ?? '',
        $data['password'] ?? ''
    );

    echo json_encode([
        'success' => true,
        'user_id' => $authResult['user']->getId(),
        'email' => $authResult['user']->getEmail(),
        'token' => $authResult['token']
    ]);

} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
} catch (RuntimeException $e) {
    http_response_code(401);
    echo json_encode(['error' => $e->getMessage()]);
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode(['error' => $e->getMessage()]);
}