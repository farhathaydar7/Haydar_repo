<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

return [
    'database' => [
        'host' => 'localhost',      // Database host
        'database' => 'gallery_db',    // Database name
        'username' => 'root',       
        'password' => '',           
        'charset' => 'utf8mb4'
    ],
    'jwt' => [
        'secret' => 'JWT_SECRET_KEY_PRO_MAX',
        'expiry' => 3600
    ],
    'uploads' => [
    'dir' => __DIR__ . '/../uploads', // No trailing slash
    'public_path' => 'uploads'        // No leading slash
]
];