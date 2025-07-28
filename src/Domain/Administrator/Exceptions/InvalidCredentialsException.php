<?php

namespace Domain\Administrator\Exceptions;

use Exception;

class InvalidCredentialsException extends Exception
{
    public function __construct(string $message = 'Invalid credentials provided', int $code = 401)
    {
        parent::__construct($message, $code);
    }
}
