<?php

namespace Domain\Profile\Exceptions;

use Exception;

class InvalidImageException extends Exception
{
    public function __construct(string $message = 'Invalid image file', int $code = 422)
    {
        parent::__construct($message, $code);
    }
}
