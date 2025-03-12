<?php
// JWT Configuration
$jwt_secret = 'my_strong_secret_key_123!@#';

// Database Configuration


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

// Allow CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Include models
require_once __DIR__ . '/models/User.Model.php';
require_once __DIR__ . '/models/Photo.Model.php';
require_once __DIR__ . '/models/Tag.Model.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    exit();
}
?>

