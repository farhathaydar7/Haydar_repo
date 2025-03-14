<?php

// Include Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables from .env file (if using Dotenv)
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

// JWT Configuration
$jwt_secret = $_ENV['JWT_SECRET'] ?? 'my_strong_secret_key_123!@#';

// Database Configuration
$dbConfig = [
    'host' => $_ENV['DB_HOST'] ?? 'localhost',
    'dbname' => $_ENV['DB_NAME'] ?? 'gallery_db',
    'username' => $_ENV['DB_USER'] ?? 'root',
    'password' => $_ENV['DB_PASS'] ?? '',
];

// Initialize Database Connection
try {
    $db = new PDO(
        "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']}",
        $dbConfig['username'],
        $dbConfig['password']
    );
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// CORS Configuration
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    exit();
}

// Other Global Settings
define('BASE_UPLOAD_DIR', __DIR__ . '/assets/photos'); // Base directory for file uploads
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB max file size
define('ALLOWED_MIME_TYPES', ['image/jpeg', 'image/png', 'image/gif']); // Allowed file types

// Helper Functions
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

?>