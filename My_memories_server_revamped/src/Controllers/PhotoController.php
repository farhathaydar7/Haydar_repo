<?php
namespace MyApp\Controllers;

use MyApp\Models\PhotoModel;
use MyApp\Services\ImageService;
use MyApp\Middleware\JwtMiddleware;

class PhotoController {
    private $photoModel;
    private $imageService;
    private $jwtMiddleware;

    public function __construct(
        PhotoModel $photoModel,
        ImageService $imageService,
        JwtMiddleware $jwtMiddleware
    ) {
        $this->photoModel = $photoModel;
        $this->imageService = $imageService;
        $this->jwtMiddleware = $jwtMiddleware;
    }

    public function getPhoto(int $photoId): array {
        $userId = $this->jwtMiddleware->handle();
        return $this->photoModel->getPhotoById($photoId, $userId);
    }

    public function uploadPhoto(array $data): array {
        $userId = $this->jwtMiddleware->handle();
        
        $imagePath = $this->imageService->processUpload(
            $data['image'],
            $userId
        );

        return $this->photoModel->create(
            $userId,
            $data['title'],
            $data['date'],
            $data['description'],
            $data['tag_id'],
            $imagePath
        );
    }
}