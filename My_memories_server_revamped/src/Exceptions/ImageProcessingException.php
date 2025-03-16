<?php
namespace MyApp\Exceptions;

class ImageProcessingException extends \RuntimeException {
    public function __construct(string $message = "Image processing error", int $code = 500) {
        parent::__construct($message, $code);
    }
}