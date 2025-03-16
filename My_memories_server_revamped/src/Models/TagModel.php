<?php

namespace MyApp\Models; 
use PDO;
use PDOException;
class TagModel extends \MyApp\Skeletons\TagSkeleton{
    private $db;

    public function __construct(\PDO $db) { // Use global namespace PDO
        $this->db = $db;
    }
    /**
     * Find or create a tag.
     */
    public function findOrCreateTag(string $tag_name, int $owner_id): int {
        $this->validateTagData($tag_name, $owner_id);

        try {
            // Check if tag exists
            $tagId = $this->getTagIdByNameAndOwner($tag_name, $owner_id);
            if ($tagId !== null) {
                return $tagId; // Return existing tag ID
            }

            // Create new tag
            return $this->createTag($tag_name, $owner_id);
        } catch (PDOException $e) {
            throw new DatabaseException("Database error: " . $e->getMessage());
        }
    }

    /**
     * Fetch all tags for a specific owner.
     */
    public function getTagsByOwner(int $owner_id): array {
        $this->validateOwnerId($owner_id);

        try {
            $stmt = $this->db->prepare("
                SELECT * FROM tags 
                WHERE tag_owner = :owner_id
            ");
            $stmt->execute([':owner_id' => $owner_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new DatabaseException("Database error: " . $e->getMessage());
        }
    }

    /**
     * Get tag ID by name and owner.
     */
    private function getTagIdByNameAndOwner(string $tag_name, int $owner_id): ?int {
        $stmt = $this->db->prepare("
            SELECT tag_id FROM tags 
            WHERE tag_name = :tag_name 
            AND tag_owner = :owner_id
        ");
        $stmt->execute([':tag_name' => $tag_name, ':owner_id' => $owner_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int)$result['tag_id'] : null;
    }

    /**
     * Create a new tag.
     */
    
public function createTag(string $tagName, int $userId): array {
    $stmt = $this->db->prepare("
        INSERT INTO tags (tag_name, tag_owner)
        VALUES (:tag_name, :tag_owner)
    ");
    
    $stmt->execute([
        ':tag_name' => $tagName,
        ':tag_owner' => $userId
    ]);
    
    return [
        'success' => true,
        'tag_id' => $this->db->lastInsertId()
    ];
}

    /**
     * Validate tag data.
     */
    protected function validateTagData(string $tag_name, int $owner_id): void { // Changed to protected
        if (empty($tag_name)) {
            throw new ValidationException("Tag name cannot be empty");
        }

        if (empty($owner_id)) {
            throw new ValidationException("Owner ID cannot be empty");
        }
    }

    /**
     * Validate owner ID (FIXED ACCESS LEVEL)
     */
    protected function validateOwnerId(int $owner_id): void { // Changed to protected
        if (empty($owner_id)) {
            throw new ValidationException("Owner ID cannot be empty");
        }
    }

    public function getTagByName(string $tagName, int $userId): ?array {
        $stmt = $this->db->prepare("
            SELECT tag_id 
            FROM tags 
            WHERE tag_name = :tag_name 
            AND tag_owner = :user_id
        ");
        
        $stmt->execute([
            ':tag_name' => $tagName,
            ':user_id' => $userId
        ]);
        
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

   
}
?>