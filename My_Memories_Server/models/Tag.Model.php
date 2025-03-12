<?php

// models/Tag.Model.php
require_once __DIR__.'/../skeletons/Tag.Skeleton.php';

class TagModel extends TagSkeleton {
    private $db;

    public function __construct() {
        global $db;
        $this->db = $db;
    }

    public function findOrCreateTag($tag_name, $owner_id) {
        // Check if tag exists
        $stmt = $this->db->prepare("
            SELECT tag_id FROM tags 
            WHERE tag_name = :tag_name 
            AND tag_owner = :owner_id
        ");
        $stmt->bindParam(':tag_name', $tag_name);
        $stmt->bindParam(':owner_id', $owner_id);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            return $result['tag_id'];
        }

        // Create new tag
        $stmt = $this->db->prepare("
            INSERT INTO tags (tag_name, tag_owner)
            VALUES (:tag_name, :owner_id)
        ");
        $stmt->bindParam(':tag_name', $tag_name);
        $stmt->bindParam(':owner_id', $owner_id);
        $stmt->execute();
        
        return $this->db->lastInsertId();
    }
}
