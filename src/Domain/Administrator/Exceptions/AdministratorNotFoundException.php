<?php

namespace Domain\Administrator\Exceptions;

use Exception;

class AdministratorNotFoundException extends Exception
{
    public function __construct(string $message = 'Administrator not found', int $code = 404)
    {
        parent::__construct($message, $code);
    }
}
