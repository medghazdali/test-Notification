<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class SendNotificationRequest
{
    /**
     * @var array<array{file_name: string, mime_type: string, file_path: string}>
     */
    public array $attachments = [];

    public function __construct(array $data = [])
    {
        $this->attachments = $data['attachments'] ?? [];
    }

    public function hasAttachments(): bool
    {
        return !empty($this->attachments);
    }
}
