<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CreateEmailTemplateRequest
{
    #[Assert\NotBlank(message: 'Name is required')]
    #[Assert\Length(max: 255, maxMessage: 'Name cannot exceed 255 characters')]
    public ?string $name = null;

    #[Assert\NotBlank(message: 'Subject template is required')]
    #[Assert\Length(max: 255, maxMessage: 'Subject template cannot exceed 255 characters')]
    public ?string $subjectTemplate = null;

    #[Assert\NotBlank(message: 'HTML body template is required')]
    public ?string $htmlBodyTemplate = null;

    #[Assert\NotBlank(message: 'Plain text body template is required')]
    public ?string $plainTextBodyTemplate = null;

    public function __construct(array $data = [])
    {
        $this->name = $data['name'] ?? null;
        $this->subjectTemplate = $data['subject_template'] ?? null;
        $this->htmlBodyTemplate = $data['html_body_template'] ?? null;
        $this->plainTextBodyTemplate = $data['plain_text_body_template'] ?? null;
    }
}
