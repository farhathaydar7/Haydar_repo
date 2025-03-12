<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__.'/../config.php';
require_once __DIR__.'/../models/User.Model.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed', 405);
    }

    $data = json_decode(file_get_contents('php://input'), true);
    
    if ($data === null || !isset($data['email'], $data['password'])) {
        throw new InvalidArgumentException('Invalid request body', 400);
    }

    $userModel = new UserModel();
    
    // Temporary debug logging
    error_log('Login attempt for: ' . $data['email']);
    
    $authResult = $userModel->authenticate($data['email'], $data['password']);
    
    // If auth fails but user exists, check legacy password format
    if (!$authResult) {
        $user = $userModel->getByEmail($data['email']);
        if ($user && !password_verify($data['password'], $user['password'])) {
            // Check if password matches legacy plaintext format
            if ($user['password'] === $data['password']) {
                // Upgrade password to hash
                $userModel->updatePassword($user['id'], $data['password']);
                $authResult = $userModel->authenticate($data['email'], $data['password']);
                error_log('Password upgraded for user: ' . $data['email']);
            }
        }
    }
    if (!$authResult) {
        throw new RuntimeException('Invalid credentials', 401);
    }

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