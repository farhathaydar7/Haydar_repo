<?php

require_once '../skeletons/User.Skeleton.php';
use Firebase\JWT\JWT;
class UserModel extends UserSkeleton {
    private $db;

    function UserModel($id = null, $username = null, $email = null, $password = null) {
        // Initialize the skeleton properties
        $this->UserSkeleton($id, $username, $email, $password);
        global $db; // Get the PDO instance from config.php
        $this->db = $db;
    }



    // Create user
    public function create($username, $email, $password) {
        $stmt = $this->db->prepare("
            INSERT INTO users (username, email, password) 
            VALUES (:username, :email, :password)
        ");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt->bindParam(':password', $hashedPassword);
        return $stmt->execute();
    }

    // Get user by ID
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update user
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
}
public function authenticate($username, $password) {
    $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return false;
}   
?>
