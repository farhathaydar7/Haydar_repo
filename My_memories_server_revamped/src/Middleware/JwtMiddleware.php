<?php
namespace MyApp\Middleware;

use MyApp\Services\JwtService;
use Exception;

class JwtMiddleware {
    private $jwtService;

    public function __construct(JwtService $jwtService) {
        $this->jwtService = $jwtService;
    }

    /**
     * Handle the JWT token and return the user ID.
     */
    public function handle(): int {
        $token = $this->getTokenFromHeader();
        $decoded = $this->jwtService->decode($token);
        return (int) $decoded['sub']; // Ensure this returns the user ID as an integer
    }

    /**
     * Extract the JWT token from the Authorization header.
     */
    private function getTokenFromHeader(): string {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            throw new Exception('Authorization header missing or invalid');
        }
        return $matches[1];
    }
}