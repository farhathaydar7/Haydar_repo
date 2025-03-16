<?php
namespace MyApp\Services;

use MyApp\Exceptions\ImageProcessingException;

class ImageService {
    private $uploadDir;
    private $publicBasePath;


public function __construct(string $uploadDir, string $publicPath) {
    $this->uploadDir = rtrim($uploadDir, '/\\'); // Ensure no trailing slashes
    $this->publicPath = rtrim($publicPath, '/');
}
    /**
     * Process a base64 image upload and save it to the server.
     */
    public function processUpload(string $base64Image, int $userId): string {
        // Extract the base64 data and MIME type
        if (!preg_match('/^data:(image\/(jpeg|png|gif|webp));base64,/', $base64Image, $matches)) {
            throw new ImageProcessingException('Invalid base64 image format');
        }

        $mimeType = $matches[1]; // e.g., "image/jpeg"
        $imageData = base64_decode(substr($base64Image, strpos($base64Image, ',') + 1));

        // Validate image data
        if (!$imageData) {
            throw new ImageProcessingException('Failed to decode base64 image');
        }

        // Create user directory
        $userDir = "{$this->uploadDir}/{$userId}";
        if (!is_dir($userDir) && !mkdir($userDir, 0755, true)) {
            throw new ImageProcessingException('Failed to create user directory');
        }

        // Save file
        $extension = explode('/', $mimeType)[1]; // e.g., "jpeg"
        $filename = uniqid('img_').'.'.$extension;
        $filePath = "{$userDir}/{$filename}";
        
        if (!file_put_contents($filePath, $imageData)) {
            throw new ImageProcessingException('Failed to save image');
        }

        return "$userId/$filename";
    }

    /**
     * Retrieve an image as a base64-encoded string.
     */
    public function getImageAsBase64(string $imageUrl): string {
        try {
            // Extract relative path from URL
            $baseUrl = 'http://localhost:8000/';
            $relativePath = str_replace($baseUrl, '', $imageUrl);
            
            // Remove duplicate "uploads" from path
            $relativePath = ltrim(str_replace('uploads/', '', $relativePath), '/');
            
            // Build correct filesystem path
            $fullPath = $this->uploadDir . DIRECTORY_SEPARATOR . $relativePath;
            $fullPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $fullPath);
    
            if (!file_exists($fullPath)) {
                throw new \Exception("Image file not found: $fullPath");
            }
    
            $imageData = file_get_contents($fullPath);
            $mimeType = mime_content_type($fullPath);
            
            return "data:$mimeType;base64," . base64_encode($imageData);
        } catch (\Exception $e) {
            throw new ImageProcessingException("Image retrieval failed: " . $e->getMessage());
        }
    }
}