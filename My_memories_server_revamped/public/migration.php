<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once __DIR__.'/../config/config.php';

class DatabaseMigrator {
    private $pdo;
    private $schema = [
        "CREATE DATABASE IF NOT EXISTS gallery_db;
        USE gallery_db;
        
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL
        );
        
        CREATE TABLE IF NOT EXISTS tags (
            tag_id INT AUTO_INCREMENT PRIMARY KEY,
            tag_name VARCHAR(50) NOT NULL,
            tag_owner INT NOT NULL,
            FOREIGN KEY (tag_owner) REFERENCES users(id) ON DELETE CASCADE
        );
        
        CREATE TABLE IF NOT EXISTS memory (
            image_id INT AUTO_INCREMENT PRIMARY KEY,
            image_url VARCHAR(255) NOT NULL,
            owner_id INT NOT NULL,
            title VARCHAR(100) NOT NULL,
            date DATE NOT NULL,
            description TEXT,
            tag_id INT,
            FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (tag_id) REFERENCES tags(tag_id) ON DELETE SET NULL
        );"
    ];

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function runMigration(): array {
        try {
            // Split into individual statements
            $queries = explode(';', $this->schema[0]);
            
            foreach ($queries as $query) {
                $query = trim($query);
                if (!empty($query)) {
                    $this->pdo->exec($query);
                }
            }
            
            return [
                'success' => true,
                'message' => 'Database schema created successfully',
                'tables' => $this->verifyMigration()
            ];
            
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return [
                'success' => false,
                'error' => 'Migration failed: ' . $e->getMessage(),
                'code' => $e->getCode()
            ];
        }
    }

    private function verifyMigration(): array {
        $stmt = $this->pdo->query("SHOW TABLES FROM gallery_db");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}

// Security check - only allow migration in development
if ($_ENV['APP_ENV'] !== 'development') {
    http_response_code(403);
    die(json_encode(['error' => 'Migrations are only allowed in development environment']));
}

// Authorization check
if (!isset($_GET['api_key']) || $_GET['api_key'] !== $_ENV['MIGRATION_KEY']) {
    http_response_code(401);
    die(json_encode(['error' => 'Invalid migration key']));
}

try {
    // Connect without specifying database
    $pdo = new PDO(
        "mysql:host=" . $_ENV['DB_HOST'],
        $_ENV['DB_USER'],
        $_ENV['DB_PASS'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    $migrator = new DatabaseMigrator($pdo);
    $result = $migrator->runMigration();
    
    http_response_code($result['success'] ? 200 : 500);
    echo json_encode($result);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Connection failed: ' . $e->getMessage()
    ]);
}