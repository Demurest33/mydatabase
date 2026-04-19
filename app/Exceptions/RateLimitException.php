<?php

namespace App\Exceptions;

use Exception;

class RateLimitException extends Exception
{
    protected int $retryAfter;

    public function __construct(string $message, int $retryAfter = 60, int $code = 429)
    {
        parent::__construct($message, $code);
        $this->retryAfter = $retryAfter;
    }

    public function getRetryAfter(): int
    {
        return $this->retryAfter;
    }
}
