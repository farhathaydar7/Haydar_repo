<?php
namespace MyApp\Services;

use MyApp\Exceptions\ImageProcessingException;

class ImageService {
    private $uploadDir;
    private $publicBasePath;

    public function __construct(string $uploadDir, string $publicBasePath = '/') {
        $this->uploadDir = rtrim($uploadDir, '/');
        $this->publicBasePath = rtrim($publicBasePath, '/');
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

        return "/uploads/{$userId}/{$filename}";
    }

    /**
     * Retrieve an image as a base64-encoded string.
     */
    public function getImageAsBase64(string $imageUrl): string {
        try {
            // Remove the public base path from the image URL
            $relativePath = str_replace($this->publicBasePath, '', $imageUrl);

            // Construct the full filesystem path
            $fullPath = $this->uploadDir . DIRECTORY_SEPARATOR . ltrim($relativePath, '/');

            // Normalize the path (resolve any '..' or '.')
            $fullPath = realpath($fullPath);

            // Validate the path
            if (!$fullPath || !file_exists($fullPath)) {
                throw new ImageProcessingException("Image file not found: $fullPath");
            }

            // Ensure the file is within the upload directory (security check)
            if (strpos($fullPath, $this->uploadDir) !== 0) {
                throw new ImageProcessingException("Invalid image path: $fullPath");
            }

            // Get MIME type and file content
            $mimeType = mime_content_type($fullPath);
            $imageData = file_get_contents($fullPath);

            return 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
        } catch (\Exception $e) {
            throw new ImageProcessingException('Image retrieval failed: ' . $e->getMessage());
        }
    }
}