<?php
require_once '../skeletons/Tag.Skeleton.php';

class TagModel extends TagSkeleton {
    private $db;

    function TagModel($tag_id = null, $tag_name = null, $tag_owner = null) {
        $this->TagSkeleton($tag_id, $tag_name, $tag_owner);
        global $db;
        $this->db = $db;
    }

   
    // Create tag
    public function create($tag_name, $tag_owner) {
        $stmt = $this->db->prepare("
            INSERT INTO tags (tag_name, tag_owner) 
            VALUES (:tag_name, :tag_owner)
        ");
        $stmt->bindParam(':tag_name', $tag_name);
        $stmt->bindParam(':tag_owner', $tag_owner);
        return $stmt->execute();
    }

    // Get tag by ID
    public function getById($tag_id) {
        $stmt = $this->db->prepare("SELECT * FROM tags WHERE tag_id = :tag_id");
        $stmt->bindParam(':tag_id', $tag_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update tag
    public function update($tag_id, $tag_name) {
        $stmt = $this->db->prepare("UPDATE tags SET tag_name = :tag_name WHERE tag_id = :tag_id");
        $stmt->bindParam(':tag_name', $tag_name);
        $stmt->bindParam(':tag_id', $tag_id);
        return $stmt->execute();
    }

    // Delete tag
    public function delete($tag_id) {
        $stmt = $this->db->prepare("DELETE FROM tags WHERE tag_id = :tag_id");
        $stmt->bindParam(':tag_id', $tag_id);
        return $stmt->execute();
    }

    // Get all tags by owner
    public function getAllByOwner($tag_owner) {
        $stmt = $this->db->prepare("SELECT * FROM tags WHERE tag_owner = :tag_owner");
        $stmt->bindParam(':tag_owner', $tag_owner);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
