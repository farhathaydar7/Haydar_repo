<?php
namespace MyApp\Utils;

use PDO;
use PDOException;

class Database {
    private static $instance = null;
    private $connection;

    private function __construct(array $config) {
        try {
            $this->connection = new PDO(
                $config['dsn'],
                $config['user'],
                $config['password']
            );
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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
}