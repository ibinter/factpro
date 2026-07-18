<?php

namespace FactPro\Exceptions;

class AuthException extends FactProException
{
    public function __construct(string $message = 'Token invalide ou expiré.', ?\Throwable $previous = null)
    {
        parent::__construct($message, 401, $previous);
    }
}
