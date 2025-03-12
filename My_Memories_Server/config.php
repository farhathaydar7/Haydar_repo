<?php


// Database configuration
$host = 'localhost';
$dbname = 'gallery_db';
$username = 'root';
$password = '';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// JWT configuration
$jwt_secret = 'my_TOP_secret_JWT_secret_KEY';

// Include models
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/PhotoModel.php';
require_once __DIR__ . '/../models/TagModel.php';
?>
