<?php
namespace MyApp\Routes;

use MyApp\Controllers\{AuthController, PhotoController};
use MyApp\Middleware\CorsMiddleware;

class ApiRoutes {
    private $authController;
    private $photoController;
    private $corsMiddleware;

    public function __construct(
        PhotoController $photoController,
        AuthController $authController,
        CorsMiddleware $corsMiddleware
    ) {
        $this->photoController = $photoController;
        $this->authController = $authController;
        $this->corsMiddleware = $corsMiddleware;
    }

    public function handle(string $method, string $uri): void {
        $this->corsMiddleware->handle();
        
        // Normalize URI
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, '/') ?: '/';

        try {
            switch(true) {
                // Authentication endpoints
                case $uri === '/login' && $method === 'POST':
                    $data = json_decode(file_get_contents('php://input'), true);
                    header('Content-Type: application/json');
                    echo json_encode($this->authController->login(
                        $data['email'],
                        $data['password']
                    ));
                    exit();

                case $uri === '/register' && $method === 'POST':
                    $data = json_decode(file_get_contents('php://input'), true);
                    header('Content-Type: application/json');
                    echo json_encode($this->authController->register($data));
                    exit();

                // Photo endpoints
                case preg_match('#^/photos/(\d+)$#', $uri, $matches) && $method === 'GET':
                    $photoId = $matches[1];
                    header('Content-Type: application/json');
                    echo json_encode($this->photoController->getPhoto($photoId));
                    exit();

                case $uri === '/photos' && $method === 'GET':
                    header('Content-Type: application/json');
                    echo json_encode($this->photoController->getAllPhotos());
                    exit();

                case $uri === '/photos' && $method === 'POST':
                    $data = json_decode(file_get_contents('php://input'), true);
                    header('Content-Type: application/json');
                    echo json_encode($this->photoController->uploadPhoto($data));
                    exit();

                // Token verification endpoint
                case $uri === '/verify-token' && $method === 'GET':
                    header('Content-Type: application/json');
                    echo json_encode($this->authController->verifyToken());
                    exit();

                default:
                    http_response_code(404);
                    header('Content-Type: application/json');
                    echo json_encode(['error' => 'Route not found']);
                    exit();
            }
        } catch (\Exception $e) {
            http_response_code($e->getCode() ?: 500);
            header('Content-Type: application/json');
            echo json_encode(['error' => $e->getMessage()]);
            exit();
        }
    }
}