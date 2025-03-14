<?php

class UserSkeleton {
    protected ?int $id;
    protected ?string $username;
    protected ?string $email;
    protected ?string $password;

    public function __construct(
        int $id = null,
        string $username = null,
        string $email = null,
        string $password = null
    ) {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
    }

    // Getters and Setters
    public function getId(): ?int {
        return $this->id;
    }
    public function setId(?int $id): void {
        $this->id = $id;
    }

    public function getUsername(): ?string {
        return $this->username;
    }
    public function setUsername(?string $username): void {
        $this->username = $username;
    }

    public function getEmail(): ?string {
        return $this->email;
    }
    public function setEmail(?string $email): void {
        $this->email = $email;
    }

    public function getPassword(): ?string {
        return $this->password;
    }
    public function setPassword(?string $password): void {
        $this->password = $password;
    }
}
?>