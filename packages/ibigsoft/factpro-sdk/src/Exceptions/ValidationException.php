<?php

namespace FactPro\Exceptions;

class ValidationException extends FactProException
{
    protected array $errors;

    public function __construct(string $message = 'Erreur de validation.', array $errors = [], ?\Throwable $previous = null)
    {
        parent::__construct($message, 422, $previous);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
