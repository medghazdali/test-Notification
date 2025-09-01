<?php

namespace App\Exception;

use Exception;

class NotificationException extends Exception
{
    public static function notFound(int $id): self
    {
        return new self(sprintf('Notification with ID %d not found', $id));
    }

    public static function cannotBeSent(string $currentStatus): self
    {
        return new self(sprintf('Notification cannot be sent. Current status: %s', $currentStatus));
    }

    public static function missingRecipient(): self
    {
        return new self('Either user_id or recipient_email must be provided');
    }

    public static function userNotFound(int $userId): self
    {
        return new self(sprintf('User with ID %d not found', $userId));
    }

    public static function emailTemplateNotFound(int $templateId): self
    {
        return new self(sprintf('Email template with ID %d not found', $templateId));
    }
}
