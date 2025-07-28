<?php

namespace Domain\Comment\Exceptions;

use Exception;

class CommentAlreadyExistsException extends Exception
{
    public function __construct(string $message = 'Comment already exists for this administrator and profile', int $code = 409)
    {
        parent::__construct($message, $code);
    }
}
