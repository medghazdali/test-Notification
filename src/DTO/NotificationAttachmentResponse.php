<?php

namespace App\DTO;

use App\Entity\NotificationAttachment;

class NotificationAttachmentResponse
{
    public function __construct(
        public readonly int $id,
        public readonly int $notificationId,
        public readonly ?string $notificationSubject,
        public readonly string $fileName,
        public readonly string $mimeType,
        public readonly string $filePath,
        public readonly string $createdAt
    ) {
    }

    public static function fromEntity(NotificationAttachment $attachment): self
    {
        return new self(
            id: $attachment->getId(),
            notificationId: $attachment->getNotification()->getId(),
            notificationSubject: $attachment->getNotification()->getSubject(),
            fileName: $attachment->getFileName(),
            mimeType: $attachment->getMimeType(),
            filePath: $attachment->getFilePath(),
            createdAt: $attachment->getCreatedAt()->format('Y-m-d H:i:s')
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'notification_id' => $this->notificationId,
            'notification_subject' => $this->notificationSubject,
            'file_name' => $this->fileName,
            'mime_type' => $this->mimeType,
            'file_path' => $this->filePath,
            'created_at' => $this->createdAt,
        ];
    }
}
