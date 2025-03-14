<?php
namespace MyApp\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService {
    private $secret;
    private $expiry;

    public function __construct(string $secret, int $expiry) {
        $this->secret = $secret;
        $this->expiry = $expiry;
    }

    public function generateToken(array $payload): string {
        $payload['exp'] = time() + $this->expiry;
        return JWT::encode($payload, $this->secret, 'HS256');
    }

    public function validateToken(string $token): array {
        return (array) JWT::decode($token, new Key($this->secret, 'HS256'));
    }
}