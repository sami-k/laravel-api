<?php

namespace Domain\Administrator\Exceptions;

use Exception;

class AdministratorAlreadyExistsException extends Exception
{
    public function __construct(string $message = 'Administrator already exists', int $code = 409)
    {
        parent::__construct($message, $code);
    }
}
