<?php
namespace MyApp\Routes;

use MyApp\Controllers\PhotoController;
use MyApp\Middleware\CorsMiddleware;

class ApiRoutes {
    private $authController;
    private $photoController;
    private $corsMiddleware;

    public function __construct(
        AuthController $authController,
        PhotoController $photoController,
        CorsMiddleware $corsMiddleware
    ) {
        $this->authController = $authController;
        $this->photoController = $photoController;
        $this->corsMiddleware = $corsMiddleware;
    }

    public function handle(string $method, string $uri): void {
        $this->corsMiddleware->handle();

        try {
            switch(true) {
                case $uri === '/login' && $method === 'POST':
                    $data = json_decode(file_get_contents('php://input'), true);
                    response($this->authController->login(
                        $data['email'],
                        $data['password']
                    ));
                    break;

                case $uri === '/register' && $method === 'POST':
                    $data = json_decode(file_get_contents('php://input'), true);
                    response($this->authController->register($data));
                    break;

                case $uri === '/photos' && $method === 'GET':
                    $photoId = $_GET['photo_id'];
                    response($this->photoController->getPhoto($photoId));
                    break;
                    
                case $uri === '/photos' && $method === 'POST':
                    $data = json_decode(file_get_contents('php://input'), true);
                    response($this->photoController->uploadPhoto($data));
                    break;
                    
                default:
                    http_response_code(404);
                    echo json_encode(['error' => 'Route not found']);
                    exit();
            }
        } catch (\Exception $e) {
            http_response_code($e->getCode() ?: 500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}