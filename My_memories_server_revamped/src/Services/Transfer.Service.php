<?php
namespace MyApp\Services;

class TransferService {
    private $baseUploadDir;

    public function __construct(string $baseUploadDir) {
        $this->baseUploadDir = $baseUploadDir;
    }

    /**
     * Upload an image to the server.
     */
    public function uploadImage(string $imageData, int $user_id, string $filename): string {
        $uploadDir = $this->baseUploadDir . "/assets/photos/$user_id/";
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0775, true)) {
                throw new Exception("Failed to create directory: $uploadDir");
            }
        }

        $filePath = $uploadDir . $filename;
        if (!file_put_contents($filePath, $imageData)) {
            throw new Exception("Failed to save image to: $filePath");
        }

        return "assets/photos/$user_id/$filename";
    }

    /**
     * Delete an image file from the server.
     */
    public function deleteImage(string $imageUrl): bool {
        $filePath = $this->baseUploadDir . '/' . $imageUrl;
        if (file_exists($filePath)) {
            if (!unlink($filePath)) {
                throw new Exception("Failed to delete old image file");
            }
        }
        return true;
    }

    /**
     * Generate a unique filename with extension based on MIME type.
     */
    public function generateFilename(string $imageData): string {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($imageData);
        $extension = explode('/', $mimeType)[1];
        if ($extension === 'jpeg') $extension = 'jpg';
        return uniqid('img_', true) . '.' . $extension;
    }
}
?>