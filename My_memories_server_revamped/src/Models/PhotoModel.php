<?php

namespace MyApp\Models;

require_once __DIR__ . '/../Skeletons/PhotoSkeleton.php';
use MyApp\Skeletons\PhotoSkeleton;

class PhotoModel extends PhotoSkeleton {
    private $db;

    public function __construct(\PDO $db) {
        parent::__construct();
        $this->db = $db;
    }

    /**
     * Create a new photo entry in the database.
     */
    public function create(int $owner_id, string $title, string $date, string $description, int $tag_id, string $image_url): string {
        $stmt = $this->db->prepare("
            INSERT INTO memory 
            (image_url, owner_id, title, date, description, tag_id) 
            VALUES 
            (:image_url, :owner_id, :title, :date, :description, :tag_id)
        ");
        $stmt->execute([
            ':image_url' => $image_url,
            ':owner_id' => $owner_id,
            ':title' => $title,
            ':date' => $date,
            ':description' => $description,
            ':tag_id' => $tag_id
        ]);

        return $image_url;
    }

    /**
     * Get photo by ID.
     */
    public function getAllPhotos(int $userId, int $page = 1, int $perPage = 20, string $search = '', string $tag = ''): array {
        $offset = ($page - 1) * $perPage;
    
        // Build the SQL query
        $sql = "SELECT * FROM memory WHERE owner_id = :userId"; // Changed 'photos' to 'memory'
        $params = ['userId' => $userId];
    
        // Add search filter
        if (!empty($search)) {
            $sql .= " AND (title LIKE :search OR description LIKE :search)";
            $params['search'] = "%$search%";
        }
    
        // Add tag filter
        if (!empty($tag)) {
            $sql .= " AND tag_id = :tag";
            $params['tag'] = $tag;
        }
    
        // Add pagination
        $sql .= " LIMIT :limit OFFSET :offset";
        $params['limit'] = $perPage; // Integer
        $params['offset'] = $offset; // Integer
    
        // Prepare the query
        $stmt = $this->db->prepare($sql);
    
        // Bind parameters with explicit types
        $stmt->bindValue(':userId', $userId, \PDO::PARAM_INT);
        if (!empty($search)) {
            $stmt->bindValue(':search', $params['search'], \PDO::PARAM_STR);
        }
        if (!empty($tag)) {
            $stmt->bindValue(':tag', $params['tag'], \PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $params['limit'], \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $params['offset'], \PDO::PARAM_INT);
    
        // Execute the query
        $stmt->execute();
    
        // Fetch and return the results
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getPhotoById(int $photo_id): array {
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
        $stmt->execute([':photo_id' => $photo_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            throw new Exception("Photo not found", 404);
        }

        return $result;
    }

    /**
     * Update a photo's details.
     */
    public function updatePhoto(int $photo_id, int $owner_id, array $updateData): array {
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
            throw new Exception("No fields to update", 400);
        }

        $sql = "UPDATE memory SET "
            . implode(', ', $fields)
            . " WHERE image_id = :photo_id AND owner_id = :owner_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $this->getPhotoById($photo_id);
    }

    /**
     * Delete a photo.
     */
    public function deletePhoto(int $photo_id, int $owner_id): bool {
        $stmt = $this->db->prepare("
            DELETE FROM memory 
            WHERE image_id = :photo_id AND owner_id = :owner_id
        ");
        return $stmt->execute([':photo_id' => $photo_id, ':owner_id' => $owner_id]);
    }
}
?>