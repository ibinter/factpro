<?php

namespace FactPro\Exceptions;

class FactProException extends \RuntimeException
{
    protected int $statusCode;

    public function __construct(string $message = '', int $statusCode = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $statusCode, $previous);
        $this->statusCode = $statusCode;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
