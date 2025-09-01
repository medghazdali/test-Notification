<?php

namespace App\Exception;

use Exception;

class UserException extends Exception
{
    public static function notFound(int $id): self
    {
        return new self(sprintf('User with ID %d not found', $id));
    }

    public static function emailAlreadyExists(string $email): self
    {
        return new self(sprintf('User with email %s already exists', $email));
    }
}
