<?php
namespace MyApp\Exceptions;

class ValidationException extends \InvalidArgumentException {
    public function __construct($message = "Invalid input", $code = 400) {
        parent::__construct($message, $code);
    }
}

class AuthenticationException extends \RuntimeException {
    public function __construct($message = "Authentication failed", $code = 401) {
        parent::__construct($message, $code);
    }
}
?>