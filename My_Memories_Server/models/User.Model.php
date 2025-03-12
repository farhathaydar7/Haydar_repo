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
        $stmt = $this->db->prepare("
            INSERT INTO users (username, email, password)
            VALUES (:username, :email, :password)
        ");
        
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        $user = new UserSkeleton(
            null,
            $data['email'], // Use email as username
            $data['email'],
            $hashedPassword
        );

        $stmt->bindValue(':username', $user->getUsername());
        $stmt->bindValue(':email', $user->getEmail());
        $stmt->bindValue(':password', $user->getPassword());
        
        $stmt->execute();
        return $this->db->lastInsertId();
    }

    // Get user by Email
    public function getByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
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
    $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($userData && password_verify($password, $userData['password'])) {
        $user = new UserSkeleton(
            $userData['id'],
            $userData['username'],
            $userData['email'],
            $userData['password']
        );

        return [
            'user' => $user,
            'token' => $this->generateJwt($user)
        ];
    }
    return null;
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
