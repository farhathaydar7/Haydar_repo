<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

return [
    'database' => [
        'dsn' => $_ENV['DB_DSN'] ?? 'mysql:host=localhost;dbname=gallery_db',
        'user' => $_ENV['DB_USER'] ?? 'root',
        'password' => $_ENV['DB_PASS'] ?? '',
    ],
    'jwt' => [
        'secret' => $_ENV['JWT_SECRET'] ?? 'default_secret',
        'expiry' => $_ENV['JWT_EXPIRY'] ?? 3600,
    ],
    'uploads' => [
        'dir' => $_ENV['UPLOAD_DIR'] ?? __DIR__ . '/../public/uploads',
        'max_size' => $_ENV['UPLOAD_MAX_SIZE'] ?? 5 * 1024 * 1024,
    ],
];