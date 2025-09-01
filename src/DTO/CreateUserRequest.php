<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CreateUserRequest
{
    #[Assert\NotBlank(message: 'Email is required')]
    #[Assert\Email(message: 'Email must be a valid email address')]
    #[Assert\Length(max: 255, maxMessage: 'Email cannot exceed 255 characters')]
    public ?string $email = null;

    #[Assert\NotBlank(message: 'First name is required')]
    #[Assert\Length(max: 255, maxMessage: 'First name cannot exceed 255 characters')]
    public ?string $firstName = null;

    #[Assert\NotBlank(message: 'Last name is required')]
    #[Assert\Length(max: 255, maxMessage: 'Last name cannot exceed 255 characters')]
    public ?string $lastName = null;

    public function __construct(array $data = [])
    {
        $this->email = $data['email'] ?? null;
        $this->firstName = $data['first_name'] ?? null;
        $this->lastName = $data['last_name'] ?? null;
    }
}
