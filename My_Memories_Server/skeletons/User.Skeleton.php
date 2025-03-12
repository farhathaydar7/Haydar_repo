<?php


class UserSkeleton {
    // Basic properties representing a user
    protected $id;
    protected $username;
    protected $email;
    protected $password;

    function UserSkeleton($id = null, $username = null, $email = null, $password = null) {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        if ($password !== null) {
            $this->password = password_hash($password, PASSWORD_BCRYPT);
        }
    }

    // Getters and Setters
    public function getId() {
        return $this->id;
    }
    public function setId($id) {
        $this->id = $id;
    }

    public function getUsername() {
        return $this->username;
    }
    public function setUsername($username) {
        $this->username = $username;
    }

    public function getEmail() {
        return $this->email;
    }
    public function setEmail($email) {
        $this->email = $email;
    }

    public function getPassword() {
        return $this->password;
    }
    public function setPassword($password) {
        $this->password = password_hash($password, PASSWORD_BCRYPT);
    }
}
?>
