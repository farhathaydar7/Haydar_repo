<?php
require_once __DIR__ . '/../vendor/autoload.php';

use MyApp\Utils\Database;
use MyApp\Services\JwtService;
use MyApp\Models\UserModel;

// Load config
$config = require __DIR__ . '/../config/config.php';

// Initialize database
$db = Database::getInstance($config['database']);

// Initialize JWT service
$jwtService = new JwtService($config['jwt']['secret'], $config['jwt']['expiry']);

// Initialize models
$userModel = new UserModel($db);



$token = $jwtService->generateToken(['user_id' => 1]);
echo "Generated Token: $token\n"; 