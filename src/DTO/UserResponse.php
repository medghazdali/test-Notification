<?php

namespace App\DTO;

use App\Entity\User;

class UserResponse
{
    public function __construct(
        public readonly int $id,
        public readonly string $email,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $createdAt,
        public readonly string $updatedAt,
        public readonly int $notificationsCount
    ) {
    }

    public static function fromEntity(User $user): self
    {
        return new self(
            id: $user->getId(),
            email: $user->getEmail(),
            firstName: $user->getFirstName(),
            lastName: $user->getLastName(),
            createdAt: $user->getCreatedAt()->format('Y-m-d H:i:s'),
            updatedAt: $user->getUpdatedAt()->format('Y-m-d H:i:s'),
            notificationsCount: $user->getNotifications()->count()
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'notifications_count' => $this->notificationsCount,
        ];
    }
}
