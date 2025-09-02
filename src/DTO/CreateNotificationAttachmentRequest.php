<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CreateNotificationAttachmentRequest
{
    #[Assert\NotBlank(message: 'Notification ID is required')]
    #[Assert\Type(type: 'integer', message: 'Notification ID must be an integer')]
    #[Assert\Positive(message: 'Notification ID must be positive')]
    public ?int $notificationId = null;

    #[Assert\NotBlank(message: 'File name is required')]
    #[Assert\Length(max: 255, maxMessage: 'File name cannot exceed 255 characters')]
    public ?string $fileName = null;

    #[Assert\NotBlank(message: 'MIME type is required')]
    #[Assert\Length(max: 100, maxMessage: 'MIME type cannot exceed 100 characters')]
    public ?string $mimeType = null;

    #[Assert\NotBlank(message: 'File path is required')]
    #[Assert\Length(max: 500, maxMessage: 'File path cannot exceed 500 characters')]
    public ?string $filePath = null;

    public function __construct(array $data = [])
    {
        $this->notificationId = isset($data['notification_id']) ? (int) $data['notification_id'] : null;
        $this->fileName = $data['file_name'] ?? null;
        $this->mimeType = $data['mime_type'] ?? null;
        $this->filePath = $data['file_path'] ?? null;
    }
}
