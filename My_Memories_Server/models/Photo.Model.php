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

        // Generate unique filename with extension based on MIME type
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($imageData);
        $extension = explode('/', $mimeType)[1];
        if ($extension === 'jpeg') $extension = 'jpg';
        $filename = uniqid('img_', true) . '.' . $extension;

        $image_url = $this->uploadImage($imageData, $owner_id, $filename);

        // Insert into database
        $stmt = $this->db->prepare("
            INSERT INTO memory 
            (image_url, owner_id, title, date, description, tag_id) 
            VALUES 
            (:image_url, :owner_id, :title, :date, :description, :tag_id)
        ");
        $stmt->bindParam(':image_url', $image_url);
        $stmt->bindParam(':owner_id', $owner_id);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':tag_id', $tag_id);
        $success = $stmt->execute();

        return $success ? $image_url : false;
    }

    public function uploadImage($imageData, $user_id, $filename) {
        $uploadDir = __DIR__ . "/../assets/photos/$user_id/";
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0775, true)) {
                throw new Exception("Failed to create directory: $uploadDir");
            }
        }

        $filePath = $uploadDir . $filename;
        if (!file_put_contents($filePath, $imageData)) {
            throw new Exception("Failed to save image to: $filePath");
        }

        return "assets/photos/$user_id/$filename";
    }
}
?>