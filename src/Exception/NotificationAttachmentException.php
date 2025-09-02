<?php

namespace App\Exception;

use Exception;

class NotificationAttachmentException extends Exception
{
    public static function notFound(int $id): self
    {
        return new self(sprintf('Notification attachment with ID %d not found', $id));
    }

    public static function notificationNotFound(int $notificationId): self
    {
        return new self(sprintf('Notification with ID %d not found', $notificationId));
    }
}
