<?php
namespace MyApp\Middleware;

use MyApp\Services\JwtService;
use MyApp\Exceptions\AuthenticationException;

class JwtMiddleware {
    private $jwtService;

    public function __construct(JwtService $jwtService) {
        $this->jwtService = $jwtService;
    }

    public function handle(): int {
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            throw new AuthenticationException('Authorization header required');
        }

        $token = str_replace('Bearer ', '', $headers['Authorization']);
        return $this->jwtService->validateToken($token)['user_id'];
    }
}