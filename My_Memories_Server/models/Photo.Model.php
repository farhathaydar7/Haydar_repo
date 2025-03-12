<?php

require_once '../skeletons/Photo.Skeleton.php';

class PhotoModel extends PhotoSkeleton {
    private $db;

    public function __construct() {
        global $db;
        $this->db = $db;
        if (!$this->db) {
            throw new Exception("Database connection not initialized");
        }
    }

    public function create($owner_id, $title, $date, $description, $tag_id, $imageData) {
        if (!$this->db) {
            throw new Exception("Database connection not available");
        }
        $image_id = uniqid('img_', true);
        $image_url = $this->uploadImage($imageData, $owner_id, $image_id);

        // Single database insertion
        $stmt = $this->db->prepare("INSERT INTO memory (image_id, image_url, owner_id, title, date, description, tag_id) VALUES (:image_id, :image_url, :owner_id, :title, :date, :description, :tag_id)");
        $stmt->bindParam(':image_id', $image_id);
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

    public function uploadImage($file, $user_id, $image_id) {
        // Remove all $_FILES references
        error_log("Processing base64 image data");

        $uploadDir = __DIR__ . "/../assets/photos/$user_id/";
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0775, true)) {
                $error = error_get_last();
                error_log("Failed to create directory $uploadDir, details: " . $error['message']);
                throw new Exception("Failed to create directory for uploads");
            }
        }

        // Handle base64 data directly
        $image = base64_decode($file);
        if ($image === false) {
            error_log("Base64 decode failed for image $image_id");
            throw new Exception('Invalid base64 data');
        }

        $fileName = "$image_id.png"; // Use UUID as filename
        $filePath = $uploadDir . $fileName;

        if (!file_put_contents($filePath, $image)) {
            $error = error_get_last();
            error_log("Failed to save image to $filePath, details: " . $error['message']);
            throw new Exception('Failed to save image');
        }

        return "assets/photos/$user_id/$fileName";
    }

}
?>
