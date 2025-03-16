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
    public function getUserIdFromToken(): int {
        return $this->jwtMiddleware->handle(); // This should return the user ID
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
            
            foreach ($photos as &$photo) {
                try {
                    $photo['image_base64'] = $this->imageService->getImageAsBase64($photo['image_url']);
                } catch (ImageProcessingException $e) {
                    $photo['image_base64'] = null;
                    $photo['image_error'] = $e->getMessage();
                }
            }
    
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
        
        // Process image upload
        $imagePath = $this->imageService->processUpload(
            $data['image'],
            $userId
        );
    
        // Handle tag creation/retrieval
        $tagId = null;
        if (!empty($data['tag'])) {
            // Check if tag exists
            $existingTag = $this->tagModel->getTagByName($data['tag'], $userId);
            
            if ($existingTag) {
                $tagId = $existingTag['tag_id'];
            } else {
                // Create new tag
                $newTag = $this->tagModel->createTag($data['tag'], $userId);
                $tagId = $newTag['tag_id'];
            }
        }
    
        return $this->photoModel->create(
            $userId,
            $data['title'],
            $data['date'],
            $data['description'],
            $tagId, // Now guaranteed to be int|null
            $imagePath
        );
    }

   
public function getImageAsBase64(string $imageUrl): array {
    try {
        $base64 = $this->imageService->getImageAsBase64($imageUrl);
        return [
            'success' => true,
            'base64' => $base64
        ];
    } catch (ImageProcessingException $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}
}