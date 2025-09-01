<?php

namespace App\Enum;

enum NotificationStatus: string
{
    case PENDING = 'pending';
    case SENT = 'sent';
    case FAILED = 'failed';
    case DELIVERED = 'delivered';
    case ARCHIVED = 'archived';

    public function getLabel(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::SENT => 'Sent',
            self::FAILED => 'Failed',
            self::DELIVERED => 'Delivered',
            self::ARCHIVED => 'Archived',
        };
    }

    public function isCompleted(): bool
    {
        return match($this) {
            self::SENT, self::DELIVERED, self::ARCHIVED => true,
            self::PENDING, self::FAILED => false,
        };
    }

    public function canBeSent(): bool
    {
        return $this === self::PENDING;
    }

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}
