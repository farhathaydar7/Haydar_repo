<?php

require_once '../skeletons/Photo.Skeleton.php';

class PhotoModel extends PhotoSkeleton {
    private $db;

    function PhotoModel($image_id = null, $image_url = null, $owner_id = null, $title = null, $date = null, $description = null, $tag_id = null) {
        $this->PhotoSkeleton($image_id, $image_url, $owner_id, $title, $date, $description, $tag_id);
        global $db;
        $this->db = $db;
    }

// Create photo (updated to handle UUID generation and image upload)
public function create($owner_id, $title, $date, $description, $tag_id, $file) {
    // Generate UUID before file upload
    $image_id = uniqid('img_', true);
    $this->setImageId($image_id);
    
    // Upload image and get URL
    $image_url = $this->uploadImage($file, $owner_id, $image_id);
    $this->setImageUrl($image_url);
    
    $stmt = $this->db->prepare("
        INSERT INTO memory (image_id, image_url, owner_id, title, date, description, tag_id)
        VALUES (:image_id, :image_url, :owner_id, :title, :date, :description, :tag_id)
    ");
    $stmt->bindParam(':image_id', $image_id);
    $stmt->bindParam(':image_url', $image_url);
    $stmt->bindParam(':owner_id', $owner_id);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':date', $date);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':tag_id', $tag_id);
    $stmt->bindParam(':image_url', $image_url);
        $stmt->bindParam(':owner_id', $owner_id);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':tag_id', $tag_id);
        return $stmt->execute();
    }

    // Get photo by ID
    public function getById($image_id) {
        $stmt = $this->db->prepare("SELECT * FROM memory WHERE image_id = :image_id");
        $stmt->bindParam(':image_id', $image_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update photo
    public function update($image_id, $title, $description, $tag_id) {
        $stmt = $this->db->prepare("
            UPDATE memory 
            SET title = :title, description = :description, tag_id = :tag_id 
            WHERE image_id = :image_id
        ");
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':tag_id', $tag_id);
        $stmt->bindParam(':image_id', $image_id);
        return $stmt->execute();
    }

    // Delete photo
    public function delete($image_id) {
        $stmt = $this->db->prepare("DELETE FROM memory WHERE image_id = :image_id");
        $stmt->bindParam(':image_id', $image_id);
        return $stmt->execute();
    }

    // Get all photos by owner
    public function getAllByOwner($owner_id) {
        $stmt = $this->db->prepare("SELECT * FROM memory WHERE owner_id = :owner_id");
        $stmt->bindParam(':owner_id', $owner_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Upload image file
    public function uploadImage($file, $user_id, $image_id) {
        // Create user-specific directory structure: ../assets/photos/[user_id]/
        $uploadDir = "../assets/photos/$user_id/";
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload error: ' . $file['error']);
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Invalid file type. Only JPG, PNG, and GIF are allowed.');
        }

        // Get file extension and create filename from image_id
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = $image_id . '.' . $ext;
        $filePath = $uploadDir . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new Exception('Failed to move uploaded file.');
        }

        return "assets/photos/$user_id/$fileName";
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload error: ' . $file['error']);
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Invalid file type. Only JPG, PNG, and GIF are allowed.');
        }

        // Get file extension from original name
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = $image_id . '.' . $ext;
        $filePath = $uploadDir . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new Exception('Failed to move uploaded file.');
        }

        return "assets/photos/$user_id/$fileName";
    }

    // Get all photos by tag
    public function getAllByTag($tag_id) {
        $stmt = $this->db->prepare("SELECT * FROM memory WHERE tag_id = :tag_id");
        $stmt->bindParam(':tag_id', $tag_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get all photos
    public function getAll() {
        $stmt = $this->db->prepare("SELECT * FROM memory");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
