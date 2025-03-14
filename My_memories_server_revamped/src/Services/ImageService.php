<?php
namespace MyApp\Services;

use MyApp\Exceptions\ImageProcessingException;

class ImageService {
    private $uploadDir;

    public function __construct(string $uploadDir) {
        $this->uploadDir = $uploadDir;
    }

    public function processUpload(string $base64Image, int $userId): string {
        $imageData = base64_decode($base64Image);
        
        // Validate image
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($imageData);
        
        if (!in_array($mimeType, ['image/jpeg', 'image/png'])) {
            throw new ImageProcessingException('Invalid image type');
        }

        // Create user directory
        $userDir = "{$this->uploadDir}/{$userId}";
        if (!is_dir($userDir) && !mkdir($userDir, 0755, true)) {
            throw new ImageProcessingException('Failed to create user directory');
        }

        // Save file
        $extension = explode('/', $mimeType)[1];
        $filename = uniqid('img_').'.'.$extension;
        $filePath = "{$userDir}/{$filename}";
        
        if (!file_put_contents($filePath, $imageData)) {
            throw new ImageProcessingException('Failed to save image');
        }

        return $filePath;
    }
}