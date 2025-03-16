<?php
require_once __DIR__ . '/../skeletons/Tag.Skeleton.php';

class TagModel extends TagSkeleton {
    private $db;

    public function __construct() {
        global $db;
        $this->db = $db;
        if (!$this->db) {
            throw new Exception("Database connection not initialized");
        }
    }

    /**
     * Find or create a tag.
     */
    public function findOrCreateTag($tag_name, $owner_id) {
        if (empty($tag_name)) {
            throw new Exception("Tag name cannot be empty", 400);
        }

        if (empty($owner_id)) {
            throw new Exception("Owner ID cannot be empty", 400);
        }

        try {
            // Check if tag exists
            $stmt = $this->db->prepare("
                SELECT tag_id FROM tags 
                WHERE tag_name = :tag_name 
                AND tag_owner = :owner_id
            ");
            $stmt->bindParam(':tag_name', $tag_name, PDO::PARAM_STR);
            $stmt->bindParam(':owner_id', $owner_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return $result['tag_id']; // Return existing tag ID
            }

            // Create new tag
            $stmt = $this->db->prepare("
                INSERT INTO tags (tag_name, tag_owner)
                VALUES (:tag_name, :owner_id)
            ");
            $stmt->bindParam(':tag_name', $tag_name, PDO::PARAM_STR);
            $stmt->bindParam(':owner_id', $owner_id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $this->db->lastInsertId(); // Return new tag ID
        } catch (PDOException $e) {
            throw new Exception("Database error: " . $e->getMessage(), 500);
        }
    }

    /**
     * Fetch all tags for a specific owner.
     */
    public function getTagsByOwner($owner_id) {
        if (empty($owner_id)) {
            throw new Exception("Owner ID cannot be empty", 400);
        }

        try {
            $stmt = $this->db->prepare("
                SELECT * FROM tags 
                WHERE tag_owner = :owner_id
            ");
            $stmt->bindParam(':owner_id', $owner_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Database error: " . $e->getMessage(), 500);
        }
    }
}
?>