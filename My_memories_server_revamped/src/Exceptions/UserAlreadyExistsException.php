<?php
namespace MyApp\Exceptions;

class UserAlreadyExistsException extends \RuntimeException {
    public function __construct($message = "User already exists", $code = 409) {
        parent::__construct($message, $code);
    }
}