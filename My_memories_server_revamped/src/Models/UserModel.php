<?php
namespace MyApp\Models;

use PDO;
use PDOException;
use RuntimeException;
use Firebase\JWT\JWT;
use MyApp\Utils\Database;
use MyApp\Skeletons\UserSkeleton;
use MyApp\Exceptions\UserAlreadyExistsException;
use MyApp\Exceptions\AuthenticationException;
use MyApp\Exceptions\ValidationException;

class UserModel extends UserSkeleton {
    private $db;
    private $jwtSecret;

    public function __construct(PDO $db, string $jwtSecret) {
        $this->db = $db;
        $this->jwtSecret = $jwtSecret;
    }

    // Create user and return ID
    public function create(array $data): int {
        $this->validateUserData($data);

        $user = new UserSkeleton(
            null,
            trim($data['username']),
            strtolower(trim($data['email'])),
            password_hash($data['password'], PASSWORD_DEFAULT)
        );

        $stmt = $this->db->prepare("
            INSERT INTO users (username, email, password)
            VALUES (:username, :email, :password)
        ");

        try {
            $stmt->execute([
                ':username' => $user->getUsername(),
                ':email' => $user->getEmail(),
                ':password' => $user->getPassword()
            ]);
            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                throw new UserAlreadyExistsException();
            }
            throw new RuntimeException('Registration failed: ' . $e->getMessage(), 500);
        }
    }

    // Get user by Email
    public function getByEmail(string $email): ?array {
        $email = strtolower($email);
        $stmt = $this->db->prepare("SELECT * FROM users WHERE LOWER(email) = :email");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    // Get user by ID
    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    // Update user password
    public function updatePassword(int $id, string $newPassword): bool {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("UPDATE users SET password = :password WHERE id = :id");
        return $stmt->execute([':password' => $hashedPassword, ':id' => $id]);
    }

    // Update user details
    public function update(int $id, string $username, string $email): bool {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET username = :username, email = :email 
            WHERE id = :id
        ");
        return $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':id' => $id
        ]);
    }

    // Delete user
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    // Authenticate user and return JWT
    public function authenticate(string $email, string $password): array {
        $user = $this->getByEmail($email);

        if (!$user || !$this->verifyPassword($password, $user['password'])) {
            throw new AuthenticationException();
        }

        return $this->createAuthResult($user);
    }

    // Validate user data
    private function validateUserData(array $data): void {
        if (!isset($data['username'], $data['email'], $data['password'])) {
            throw new ValidationException('Missing required fields');
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException('Invalid email format');
        }

        if (strlen($data['password']) < 8) {
            throw new ValidationException('Password must be at least 8 characters');
        }
    }

    // Verify password
    private function verifyPassword(string $password, string $hashedPassword): bool {
        return password_verify($password, $hashedPassword) || $password === $hashedPassword; // Legacy support
    }

    // Create authentication result
    private function createAuthResult(array $user): array {
        $userSkeleton = new UserSkeleton(
            $user['id'],
            $user['username'],
            $user['email'],
            $user['password']
        );

        return [
            'user' => $userSkeleton,
            'token' => $this->generateJwt($userSkeleton)
        ];
    }

    // Token verification
    public function verifyToken(string $token): array {
        try {
            $decoded = JWT::decode($token, $this->jwtSecret, ['HS256']);
            return (array)$decoded;
        } catch (\Exception $e) {
            throw new AuthenticationException('Token verification failed: ' . $e->getMessage());
        }
    }

    // Generate JWT token
    private function generateJwt(UserSkeleton $user): string {
        $payload = [
            'iss' => 'my_memories',
            'sub' => $user->getId(),
            'email' => $user->getEmail(),
            'exp' => time() + 3600 // 1 hour
        ];

        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }
}
?>