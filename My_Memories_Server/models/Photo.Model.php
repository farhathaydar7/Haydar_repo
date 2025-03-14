<?php
require_once __DIR__ . '/../skeletons/Photo.Skeleton.php';

class PhotoModel extends PhotoSkeleton {
    private $db;

    public function __construct() {
        global $db;
        $this->db = $db;
        if (!$this->db) {
            throw new Exception("Database connection not initialized");
        }
    }

    /**
     * Create a new photo entry in the database.
     */
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

        // Upload the image and get the URL
        $image_url = $this->uploadImage($imageData, $owner_id, $filename);

        // Insert into database
        $stmt = $this->db->prepare("
            INSERT INTO memory 
            (image_url, owner_id, title, date, description, tag_id) 
            VALUES 
            (:image_url, :owner_id, :title, :date, :description, :tag_id)
        ");
        $stmt->bindParam(':image_url', $image_url);
        $stmt->bindParam(':owner_id', $owner_id, PDO::PARAM_INT);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':tag_id', $tag_id, PDO::PARAM_INT);
        $stmt->execute();

        return $image_url;
    }

    /**
     * Upload an image to the server.
     */
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

    public function getPhotoById($photo_id) {
        if (!$this->db) {
            throw new Exception("Database connection not available");
        }
    
        $stmt = $this->db->prepare("
            SELECT 
                m.image_id AS id, 
                m.image_url, 
                m.owner_id, 
                m.title, 
                m.date, 
                m.description, 
                t.tag_name
            FROM memory m
            LEFT JOIN tags t ON m.tag_id = t.tag_id
            WHERE m.image_id = :photo_id
        ");
        $stmt->bindParam(':photo_id', $photo_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$result) {
            throw new Exception("Photo not found", 404);
        }
    
        return $result;
    }

    /**
     * Update a photo's details.
     */
    public function updatePhoto($photo_id, $owner_id, $updateData) {
        if (!$this->db) {
            throw new Exception("Database connection not available");
        }

        // Handle image update
        if (isset($updateData['image'])) {
            // Get existing photo data
            $existingPhoto = $this->getPhotoById($photo_id);
            
            // Upload new image
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->buffer($updateData['image']);
            $extension = explode('/', $mimeType)[1];
            if ($extension === 'jpeg') $extension = 'jpg';
            $filename = uniqid('img_', true) . '.' . $extension;
            
            $newImageUrl = $this->uploadImage(
                $updateData['image'],
                $owner_id,
                $filename
            );
            
            // Delete old image
            $this->deleteImage($existingPhoto['image_url']);
            
            $updateData['image_url'] = $newImageUrl;
            unset($updateData['image']);
        }

        // Build dynamic update query
        $fields = [];
        $params = [':photo_id' => $photo_id, ':owner_id' => $owner_id];

        foreach ($updateData as $key => $value) {
            if ($value !== null) {
                $fields[] = "$key = :$key";
                $params[":$key"] = $value;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE memory SET "
            . implode(', ', $fields)
            . " WHERE image_id = :photo_id AND owner_id = :owner_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $this->getPhotoById($photo_id);
    }

    /**
     * Delete an image file from the server.
     */
    private function deleteImage($imageUrl) {
        $filePath = __DIR__ . '/../' . $imageUrl;
        if (file_exists($filePath)) {
            if (!unlink($filePath)) {
                throw new Exception("Failed to delete old image file");
            }
        }
        return true;
    }
}
?>