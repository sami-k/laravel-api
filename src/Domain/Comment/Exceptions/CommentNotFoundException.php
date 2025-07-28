<?php

namespace Domain\Comment\Exceptions;

use Exception;

class CommentNotFoundException extends Exception
{
    public function __construct(string $message = 'Comment not found', int $code = 404)
    {
        parent::__construct($message, $code);
    }
}
