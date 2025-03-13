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

    // Existing create method
    public function create($owner_id, $title, $date, $description, $tag_id, $imageData) {
        // ... existing create implementation ...
    }

    // Existing uploadImage method
    public function uploadImage($imageData, $user_id, $filename) {
        // ... existing uploadImage implementation ...
    }

    // NEW METHOD: Get photo by ID
    public function getPhotoById($photo_id) {
        if (!$this->db) {
            throw new Exception("Database connection not available");
        }

        $stmt = $this->db->prepare("
            SELECT * FROM memory 
            WHERE id = :photo_id
        ");
        $stmt->bindParam(':photo_id', $photo_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // NEW METHOD: Update photo
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
            . " WHERE id = :photo_id AND owner_id = :owner_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $this->getPhotoById($photo_id);
    }

    // NEW METHOD: Delete image file
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