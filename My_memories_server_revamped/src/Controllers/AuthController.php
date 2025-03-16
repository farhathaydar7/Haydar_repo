<?php
namespace MyApp\Controllers;

use MyApp\Models\UserModel;
use MyApp\Services\JwtService;
use MyApp\Exceptions\ValidationException;
use MyApp\Exceptions\AuthenticationException;
use MyApp\Exceptions\UserAlreadyExistsException;

class AuthController {
    private $userModel;
    private $jwtService;

    public function __construct(UserModel $userModel, JwtService $jwtService) {
        $this->userModel = $userModel;
        $this->jwtService = $jwtService;
    }

    public function register(array $data): array {
        try {
            $userId = $this->userModel->create($data);
            return [
                'success' => true,
                'user_id' => $userId
            ];
        } catch (UserAlreadyExistsException $e) {
            throw $e;
        } catch (ValidationException $e) {
            throw $e;
        }
    }

    public function login(string $email, string $password): array {
        if (empty($email) || empty($password)) {
            throw new ValidationException('Email and password are required');
        }

        $authResult = $this->userModel->authenticate($email, $password);

        if (!$authResult) {
            throw new AuthenticationException('Invalid credentials');
        }

        // Ensure we have a user object with getId() method
        $user = $authResult['user'];
        $token = $this->jwtService->generateToken([
            'sub' => $user->getId(),
            'email' => $user->getEmail()
        ]);

        return [
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail()
            ]
        ];
    }

    public function verifyToken(): array {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        
        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            throw new AuthenticationException('Authorization header missing or invalid');
        }

        $token = $matches[1];
        
        try {
            // Convert to array if using Firebase JWT decoder
            $decoded = (array) $this->userModel->verifyToken($token);
            
            // Validate required claims
            if (!isset($decoded['sub'])) {
                throw new AuthenticationException('Invalid token: missing subject');
            }

            return [
                'success' => true,
                'user' => [
                    'id' => $decoded['sub'],
                    'email' => $decoded['email'] ?? null
                ]
            ];
        } catch (AuthenticationException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
?>