<?php

require_once '../skeletons/Photo.Skeleton.php';

class PhotoModel extends PhotoSkeleton {
    private $db;

    function PhotoModel($image_id = null, $image_url = null, $owner_id = null, $title = null, $date = null, $description = null, $tag_id = null) {
        $this->PhotoSkeleton($image_id, $image_url, $owner_id, $title, $date, $description, $tag_id);
        global $db;
        $this->db = $db;
    }


    // Create photo
    public function create($image_url, $owner_id, $title, $date, $description, $tag_id) {
        $stmt = $this->db->prepare("
            INSERT INTO memory (image_url, owner_id, title, date, description, tag_id) 
            VALUES (:image_url, :owner_id, :title, :date, :description, :tag_id)
        ");
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
}
?>
