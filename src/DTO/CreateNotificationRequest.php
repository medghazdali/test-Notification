<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CreateNotificationRequest
{
    #[Assert\NotBlank(message: 'Subject is required')]
    #[Assert\Length(max: 255, maxMessage: 'Subject cannot exceed 255 characters')]
    public ?string $subject = null;

    #[Assert\NotBlank(message: 'Body is required')]
    public ?string $body = null;

    #[Assert\Type(type: 'integer', message: 'User ID must be an integer')]
    #[Assert\Positive(message: 'User ID must be positive')]
    public ?int $userId = null;

    #[Assert\Email(message: 'Recipient email must be a valid email address')]
    #[Assert\Length(max: 255, maxMessage: 'Recipient email cannot exceed 255 characters')]
    public ?string $recipientEmail = null;

    #[Assert\Type(type: 'integer', message: 'Email template ID must be an integer')]
    #[Assert\Positive(message: 'Email template ID must be positive')]
    public ?int $emailTemplateId = null;

    /**
     * @var array<array{file_name: string, mime_type: string, file_path: string}>
     */
    public array $attachments = [];

    public function __construct(array $data = [])
    {
        $this->subject = $data['subject'] ?? null;
        $this->body = $data['body'] ?? null;
        $this->userId = isset($data['user_id']) ? (int) $data['user_id'] : null;
        $this->recipientEmail = $data['recipient_email'] ?? null;
        $this->emailTemplateId = isset($data['email_template_id']) ? (int) $data['email_template_id'] : null;
        $this->attachments = $data['attachments'] ?? [];
    }

    public function hasRecipient(): bool
    {
        return $this->userId !== null || $this->recipientEmail !== null;
    }
}
