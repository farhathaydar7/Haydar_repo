<?php
namespace MyApp\Middleware;

use MyApp\Services\JwtService;
use MyApp\Exceptions\AuthenticationException;

class JwtMiddleware {
    private $jwtService;

    public function __construct(JwtService $jwtService) {
        $this->jwtService = $jwtService;
    }

    public function handle(): array {
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            throw new AuthenticationException('Authorization header not found');
        }

        $authHeader = $headers['Authorization'];
        list($jwt) = sscanf($authHeader, 'Bearer %s');

        if (!$jwt) {
            throw new AuthenticationException('Token not provided');
        }

        try {
            return $this->jwtService->validateToken($jwt);
        } catch (\Exception $e) {
            throw new AuthenticationException('Invalid token');
        }
    }
}
?>