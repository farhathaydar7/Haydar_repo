<?php
namespace MyApp\Exceptions;

class AuthenticationException extends \RuntimeException {
    public function __construct($message = "Authentication failed", $code = 401) {
        parent::__construct($message, $code);
    }
}