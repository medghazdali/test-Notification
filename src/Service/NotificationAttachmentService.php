<?php

namespace App\Service;

use App\DTO\CreateNotificationAttachmentRequest;
use App\DTO\NotificationAttachmentResponse;
use App\Entity\NotificationAttachment;
use App\Exception\NotificationAttachmentException;
use App\Repository\NotificationAttachmentRepository;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class NotificationAttachmentService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        private NotificationAttachmentRepository $attachmentRepository,
        private NotificationRepository $notificationRepository
    ) {
    }

    public function createAttachment(CreateNotificationAttachmentRequest $request): NotificationAttachmentResponse
    {
        // Validate the request
        $errors = $this->validator->validate($request);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            throw new \InvalidArgumentException('Validation failed: ' . implode(', ', $errorMessages));
        }

        // Verify notification exists
        $notification = $this->notificationRepository->find($request->notificationId);
        if (!$notification) {
            throw NotificationAttachmentException::notificationNotFound($request->notificationId);
        }

        // Create attachment entity
        $attachment = new NotificationAttachment();
        $attachment->setNotification($notification);
        $attachment->setFileName($request->fileName);
        $attachment->setMimeType($request->mimeType);
        $attachment->setFilePath($request->filePath);

        // Validate the entity
        $errors = $this->validator->validate($attachment);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            throw new \InvalidArgumentException('Entity validation failed: ' . implode(', ', $errorMessages));
        }

        // Persist and flush
        $this->entityManager->persist($attachment);
        $this->entityManager->flush();

        return NotificationAttachmentResponse::fromEntity($attachment);
    }

    public function getAttachment(int $id): NotificationAttachmentResponse
    {
        $attachment = $this->attachmentRepository->find($id);
        if (!$attachment) {
            throw NotificationAttachmentException::notFound($id);
        }

        return NotificationAttachmentResponse::fromEntity($attachment);
    }

    public function getAllAttachments(): array
    {
        $attachments = $this->attachmentRepository->findAll();
        
        return array_map(
            fn(NotificationAttachment $attachment) => NotificationAttachmentResponse::fromEntity($attachment),
            $attachments
        );
    }

    public function getAttachmentsByNotification(int $notificationId): array
    {
        $notification = $this->notificationRepository->find($notificationId);
        if (!$notification) {
            throw NotificationAttachmentException::notificationNotFound($notificationId);
        }

        $attachments = $this->attachmentRepository->findByNotification($notification);
        
        return array_map(
            fn(NotificationAttachment $attachment) => NotificationAttachmentResponse::fromEntity($attachment),
            $attachments
        );
    }

    public function updateAttachment(int $id, CreateNotificationAttachmentRequest $request): NotificationAttachmentResponse
    {
        $attachment = $this->attachmentRepository->find($id);
        if (!$attachment) {
            throw NotificationAttachmentException::notFound($id);
        }

        // Validate the request
        $errors = $this->validator->validate($request);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            throw new \InvalidArgumentException('Validation failed: ' . implode(', ', $errorMessages));
        }

        // Verify notification exists if it's being changed
        if ($request->notificationId !== $attachment->getNotification()->getId()) {
            $notification = $this->notificationRepository->find($request->notificationId);
            if (!$notification) {
                throw NotificationAttachmentException::notificationNotFound($request->notificationId);
            }
            $attachment->setNotification($notification);
        }

        // Update the attachment
        $attachment->setFileName($request->fileName);
        $attachment->setMimeType($request->mimeType);
        $attachment->setFilePath($request->filePath);

        // Validate the entity
        $errors = $this->validator->validate($attachment);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            throw new \InvalidArgumentException('Entity validation failed: ' . implode(', ', $errorMessages));
        }

        $this->entityManager->flush();

        return NotificationAttachmentResponse::fromEntity($attachment);
    }

    public function deleteAttachment(int $id): void
    {
        $attachment = $this->attachmentRepository->find($id);
        if (!$attachment) {
            throw NotificationAttachmentException::notFound($id);
        }

        $this->entityManager->remove($attachment);
        $this->entityManager->flush();
    }
}
