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

    public function handleGetRequest(?string $photoId = null, int $page = 1, int $perPage = 20): array {
        $userId = $this->jwtMiddleware->handle();

        if ($photoId) {
            return $this->getPhoto((int)$photoId, $userId);
        }
        return $this->getAllPhotos($userId, $page, $perPage);
    }

    public function getPhoto(int $photoId, int $userId): array {
        try {
            $photo = $this->photoModel->getPhotoById($photoId, $userId);
            return [
                'success' => true,
                'data' => $photo
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function getAllPhotos(int $userId, int $page = 1, int $perPage = 20): array {
        try {
            $photos = $this->photoModel->getAllPhotos($userId, $page, $perPage);
            return [
                'success' => true,
                'data' => $photos,
                'pagination' => [
                    'page' => $page,
                    'per_page' => $perPage
                ]
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
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