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
        // Validate input
        if (empty($email) || empty($password)) {
            throw new ValidationException('Email and password are required');
        }

        // Authenticate user
        $authResult = $this->userModel->authenticate($email, $password);

        if (!$authResult) {
            throw new AuthenticationException('Invalid credentials');
        }

        // Generate JWT token
        $token = $this->jwtService->generateToken([
            'user_id' => $authResult['user']->getId(),
            'email' => $authResult['user']->getEmail()
        ]);

        return [
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $authResult['user']->getId(),
                'email' => $authResult['user']->getEmail()
            ]
        ];
    }
}
?>