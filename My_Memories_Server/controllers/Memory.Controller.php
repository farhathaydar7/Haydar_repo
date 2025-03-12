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
                'iss' => 'your-domain.com',
                'aud' => 'your-domain.com',
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

    // Existing methods (e.g. createPhoto, getPhotosByOwner, etc.) remain unchanged
    public function createPhoto($image_url, $owner_id, $title, $date, $description, $tag_id) {
        return $this->photoModel->create($image_url, $owner_id, $title, $date, $description, $tag_id);
    }

    public function getPhotosByOwner($owner_id) {
        return $this->photoModel->getAllByOwner($owner_id);
    }
}
?>
