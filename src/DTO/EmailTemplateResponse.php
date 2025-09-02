<?php

namespace App\DTO;

use App\Entity\EmailTemplate;

class EmailTemplateResponse
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $subjectTemplate,
        public readonly string $htmlBodyTemplate,
        public readonly string $plainTextBodyTemplate,
        public readonly string $createdAt,
        public readonly string $updatedAt,
        public readonly int $notificationsCount
    ) {
    }

    public static function fromEntity(EmailTemplate $emailTemplate): self
    {
        return new self(
            id: $emailTemplate->getId(),
            name: $emailTemplate->getName(),
            subjectTemplate: $emailTemplate->getSubjectTemplate(),
            htmlBodyTemplate: $emailTemplate->getHtmlBodyTemplate(),
            plainTextBodyTemplate: $emailTemplate->getPlainTextBodyTemplate(),
            createdAt: $emailTemplate->getCreatedAt()->format('Y-m-d H:i:s'),
            updatedAt: $emailTemplate->getUpdatedAt()->format('Y-m-d H:i:s'),
            notificationsCount: $emailTemplate->getNotifications()->count()
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'subject_template' => $this->subjectTemplate,
            'html_body_template' => $this->htmlBodyTemplate,
            'plain_text_body_template' => $this->plainTextBodyTemplate,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'notifications_count' => $this->notificationsCount,
        ];
    }
}
