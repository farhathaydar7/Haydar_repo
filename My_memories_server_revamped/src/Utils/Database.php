<?php
namespace MyApp\Utils;

use PDO;
use PDOException;

class Database {
    private static $instance = null;
    private $connection;

    private function __construct(array $config) {
        try {
            $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
            
            $this->connection = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (PDOException $e) {
            throw new \RuntimeException("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance(array $config): PDO {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }
        return self::$instance->connection;
    }

    // Prevent cloning and serialization
    private function __clone() {}
    public function __wakeup() {}
}