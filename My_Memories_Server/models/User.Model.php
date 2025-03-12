<?php

require_once __DIR__ . '/../vendor/autoload.php';

require_once '../skeletons/User.Skeleton.php';
use Firebase\JWT\JWT;
class UserModel extends UserSkeleton {
    private $db;

    public function __construct() {
        parent::__construct();
        global $db;
        $this->db = $db;
    }



    // Create user and return ID
    public function create(array $data) {
        // Validate required fields
        if (!isset($data['username'], $data['email'], $data['password'])) {
            throw new InvalidArgumentException('Missing required fields', 400);
        }

        // Normalize email
        $data['email'] = strtolower(trim($data['email']));
        $data['username'] = trim($data['username']);

        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email format', 400);
        }

        // Validate password strength
        if (strlen($data['password']) < 8) {
            throw new InvalidArgumentException('Password must be at least 8 characters', 400);
        }

        // Hash password first
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        $user = new UserSkeleton(
            null,
            $data['username'],
            $data['email'],
            $hashedPassword  // Store hashed password in the object
        );

        $stmt = $this->db->prepare("
            INSERT INTO users (username, email, password)
            VALUES (:username, :email, :password)
        ");

        try {
            $stmt->execute([
                ':username' => $user->getUsername(),
                ':email' => $user->getEmail(),
                ':password' => $user->getPassword()  // Use the hashed value
            ]);
            
            error_log("User created: " . $data['email']);
            return $this->db->lastInsertId();
            
        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            if ($e->getCode() == '23000') {
                throw new RuntimeException('Email or username already exists', 409);
            }
            throw new RuntimeException('Registration failed', 500);
        }
    }

    // Get user by Email
    public function getByEmail($email) {
        // Convert email to lowercase for case-insensitive search
        $email = strtolower($email);
        $stmt = $this->db->prepare("SELECT * FROM users WHERE LOWER(email) = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get user by ID
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update user
    public function updatePassword($id, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("UPDATE users SET password = :password WHERE id = :id");
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function update($id, $username, $email) {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET username = :username, email = :email 
            WHERE id = :id
        ");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Delete user
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

// Authenticate user and return JWT
public function authenticate(string $email, string $password): ?array {
    $email = strtolower($email);
    $user = $this->getByEmail($email);

    if (!$user) {
        error_log("User not found: $email");
        return null;
    }

    if (password_verify($password, $user['password'])) {
        return $this->createAuthResult($user);
    }

    // Legacy password check
    if ($user['password'] === $password) {
        $this->updatePassword($user['id'], $password);
        return $this->createAuthResult($user);
    }

    error_log("Password mismatch for user: $email");
    return null;
}

private function createAuthResult(array $user): array {
    return [
        'user' => new UserSkeleton(
            $user['id'],
            $user['username'],
            $user['email'],
            $user['password']
        ),
        'token' => $this->generateJwt(new UserSkeleton(
            $user['id'],
            $user['username'],
            $user['email'],
            $user['password']
        ))
    ];
}

// Generate JWT token
private function generateJwt(UserSkeleton $user): string {
    global $jwt_secret;
    
    $payload = [
        'iss' => 'my_memories',
        'sub' => $user->getId(),
        'email' => $user->getEmail(),
        'exp' => time() + 3600 // 1 hour
    ];

    return JWT::encode($payload, $jwt_secret, 'HS256');
}
}
?>
