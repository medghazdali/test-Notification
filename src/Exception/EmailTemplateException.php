<?php

namespace App\Exception;

use Exception;

class EmailTemplateException extends Exception
{
    public static function notFound(int $id): self
    {
        return new self(sprintf('Email template with ID %d not found', $id));
    }

    public static function nameAlreadyExists(string $name): self
    {
        return new self(sprintf('Email template with name "%s" already exists', $name));
    }

    public static function cannotDeleteInUse(int $id): self
    {
        return new self(sprintf('Cannot delete email template with ID %d because it is being used by notifications', $id));
    }
}
