<?php
namespace MyApp\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Exception;

class JwtService {
    private $secret;
    private $expiry;

    public function __construct(string $secret, int $expiry) {
        $this->secret = $secret;
        $this->expiry = $expiry;
    }

    /**
     * Decode a JWT token.
     */
    public function decode(string $token): array {
        try {
            return (array) JWT::decode($token, new Key($this->secret, 'HS256'));
        } catch (ExpiredException $e) {
            throw new Exception('Token has expired');
        } catch (SignatureInvalidException $e) {
            throw new Exception('Invalid token signature');
        } catch (Exception $e) {
            throw new Exception('Invalid token');
        }
    }

    /**
     * Generate a JWT token.
     */
    public function generateToken(array $payload): string {
        $payload['exp'] = time() + $this->expiry;
        return JWT::encode($payload, $this->secret, 'HS256');
    }

    /**
     * Validate a JWT token.
     */
    public function validateToken(string $token): array {
        return $this->decode($token);
    }
}