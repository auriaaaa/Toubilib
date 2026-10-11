<?php
namespace toubilib\application\exceptions;

class ValidationException extends \DomainException {
    private array $errors;

    public function __construct(array $errors, string $message = 'Validation failed') {
        parent::__construct($message);
        $this->errors = $errors;
    }

    public function getErrors(): array {
        return $this->errors;
    }
}