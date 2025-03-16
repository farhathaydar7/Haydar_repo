<?php
namespace MyApp\Exceptions;

class ValidationException extends \RuntimeException {
    public function __construct($message = "Validation error", $code = 400) {
        parent::__construct($message, $code);
    }
}