<?php

namespace Domain\Profile\Exceptions;

use Exception;

class ProfileNotFoundException extends Exception
{
    public function __construct(string $message = 'Profile not found', int $code = 404)
    {
        parent::__construct($message, $code);
    }
}
