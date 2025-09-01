<?php

namespace App\DTO;

use App\Entity\Notification;

class NotificationResponse
{
    public function __construct(
        public readonly int $id,
        public readonly ?int $userId,
        public readonly ?string $userName,
        public readonly ?string $userEmail,
        public readonly ?string $recipientEmail,
        public readonly string $subject,
        public readonly string $body,
        public readonly string $status,
        public readonly string $statusLabel,
        public readonly string $createdAt,
        public readonly ?string $sentAt,
        public readonly int $attachmentsCount,
        public readonly ?int $emailTemplateId
    ) {
    }

    public static function fromEntity(Notification $notification): self
    {
        $user = $notification->getUser();
        $userName = $user ? $user->getFirstName() . ' ' . $user->getLastName() : null;

        return new self(
            id: $notification->getId(),
            userId: $user?->getId(),
            userName: $userName,
            userEmail: $user?->getEmail(),
            recipientEmail: $notification->getRecipientEmail(),
            subject: $notification->getSubject(),
            body: $notification->getBody(),
            status: $notification->getStatus()->value,
            statusLabel: $notification->getStatus()->getLabel(),
            createdAt: $notification->getCreatedAt()->format('Y-m-d H:i:s'),
            sentAt: $notification->getSentAt()?->format('Y-m-d H:i:s'),
            attachmentsCount: $notification->getAttachments()->count(),
            emailTemplateId: $notification->getEmailTemplate()?->getId()
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'user_name' => $this->userName,
            'user_email' => $this->userEmail,
            'recipient_email' => $this->recipientEmail,
            'subject' => $this->subject,
            'body' => $this->body,
            'status' => $this->status,
            'status_label' => $this->statusLabel,
            'created_at' => $this->createdAt,
            'sent_at' => $this->sentAt,
            'attachments_count' => $this->attachmentsCount,
            'email_template_id' => $this->emailTemplateId,
        ];
    }
}
