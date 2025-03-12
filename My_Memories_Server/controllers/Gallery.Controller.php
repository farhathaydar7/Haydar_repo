<?php
// backend/controllers/GalleryController.php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/PhotoModel.php';
require_once __DIR__ . '/../models/TagModel.php';
use Firebase\JWT\JWT;

class GalleryController {
    private $userModel;
    private $photoModel;
    private $tagModel;

    public function __construct() {
        $this->userModel = new UserModel();
        $this->photoModel = new PhotoModel();
        $this->tagModel = new TagModel();
    }

    // Login method for JWT authentication
    public function login($username, $password) {
        $user = $this->userModel->authenticate($username, $password);
        if ($user) {
            global $jwt_secret;
            $payload = [
                'iss' => 'domain.com',
                'aud' => 'domain.com',
                'iat' => time(),
                'exp' => time() + (60 * 60), // 1 hour expiration
                'data' => [
                    'id' => $user['id'],
                    'username' => $user['username']
                ]
            ];
            $jwt = JWT::encode($payload, $jwt_secret);
            return ['token' => $jwt];
        }
        return false;
    }

    public function createPhoto($owner_id, $title, $date, $description, $tag_id, $file) {
        return $this->photoModel->create($owner_id, $title, $date, $description, $tag_id, $file);
    }

    public function getPhotoById($image_id) {
        return $this->photoModel->getById($image_id);
    }

    public function updatePhoto($image_id, $title, $description, $tag_id) {
        return $this->photoModel->update($image_id, $title, $description, $tag_id);
    }

    public function deletePhoto($image_id) {
        return $this->photoModel->delete($image_id);
    }

    public function getPhotosByOwner($owner_id) {
        return $this->photoModel->getAllByOwner($owner_id);
    }

    // Get photos by tag
    public function getPhotosByTag($tag_id) {
        return $this->photoModel->getAllByTag($tag_id);
    }

    // Get all photos
    public function getAllPhotos() {
        return $this->photoModel->getAll();
    }

    // Add Tag method
    public function createTag($owner_id, $name) {
        return $this->tagModel->create($owner_id, $name);
    }

    // Get tag by ID
    public function getTagById($tag_id) {
        return $this->tagModel->getById($tag_id);
    }

    // Get all tags by owner
    public function getTagsByOwner($owner_id) {
        return $this->tagModel->getAllByOwner($owner_id);
    }

    // Update tag
    public function updateTag($tag_id, $name) {
        return $this->tagModel->update($tag_id, $name);
    }

    // Delete tag
    public function deleteTag($tag_id) {
        return $this->tagModel->delete($tag_id);
    }

    // Register a new user
    public function registerUser($username, $password) {
        return $this->userModel->create($username, $password);
    }

    // Get user by ID
    public function getUserById($user_id) {
        return $this->userModel->getById($user_id);
    }

    // Update user
    public function updateUser($user_id, $username, $password) {
        return $this->userModel->update($user_id, $username, $password);
    }

    // Delete user
    public function deleteUser($user_id) {
        return $this->userModel->delete($user_id);
    }
}
?>
