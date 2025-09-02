<?php

namespace App\Service;

use App\DTO\CreateNotificationRequest;
use App\DTO\NotificationResponse;
use App\DTO\SendNotificationRequest;
use App\Entity\EmailTemplate;
use App\Entity\Notification;
use App\Entity\User;
use App\Enum\NotificationStatus;
use App\Exception\NotificationException;
use App\Repository\EmailTemplateRepository;
use App\Repository\NotificationAttachmentRepository;
use App\Repository\NotificationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class NotificationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        private NotificationRepository $notificationRepository,
        private UserRepository $userRepository,
        private EmailTemplateRepository $emailTemplateRepository,
        private NotificationAttachmentRepository $attachmentRepository
    ) {
    }

    public function createNotification(CreateNotificationRequest $request): NotificationResponse
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

        // Check if recipient is provided
        if (!$request->hasRecipient()) {
            throw NotificationException::missingRecipient();
        }

        // Create notification entity
        $notification = new Notification();
        $notification->setSubject($request->subject);
        $notification->setBody($request->body);

        // Set user if provided
        if ($request->userId !== null) {
            $user = $this->userRepository->find($request->userId);
            if (!$user) {
                throw NotificationException::userNotFound($request->userId);
            }
            $notification->setUser($user);
        }

        // Set recipient email if provided
        if ($request->recipientEmail !== null) {
            $notification->setRecipientEmail($request->recipientEmail);
        }

        // Set email template if provided
        if ($request->emailTemplateId !== null) {
            $emailTemplate = $this->emailTemplateRepository->find($request->emailTemplateId);
            if (!$emailTemplate) {
                throw NotificationException::emailTemplateNotFound($request->emailTemplateId);
            }
            $notification->setEmailTemplate($emailTemplate);
        }

        // Validate the entity
        $errors = $this->validator->validate($notification);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            throw new \InvalidArgumentException('Entity validation failed: ' . implode(', ', $errorMessages));
        }

        // Persist and flush
        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        // Create attachments if provided
        if (!empty($request->attachments)) {
            $this->createAttachmentsForNotification($notification, $request->attachments);
        }

        return NotificationResponse::fromEntity($notification);
    }

    public function getNotification(int $id): NotificationResponse
    {
        $notification = $this->notificationRepository->find($id);
        if (!$notification) {
            throw NotificationException::notFound($id);
        }

        return NotificationResponse::fromEntity($notification);
    }

    public function getAllNotifications(): array
    {
        $notifications = $this->notificationRepository->findAll();
        
        return array_map(
            fn(Notification $notification) => NotificationResponse::fromEntity($notification),
            $notifications
        );
    }

    public function getPendingNotifications(): array
    {
        $notifications = $this->notificationRepository->findPendingNotifications();
        
        return array_map(
            fn(Notification $notification) => NotificationResponse::fromEntity($notification),
            $notifications
        );
    }

    public function sendNotification(int $id, ?SendNotificationRequest $sendRequest = null): NotificationResponse
    {
        $notification = $this->notificationRepository->find($id);
        if (!$notification) {
            throw NotificationException::notFound($id);
        }

        if (!$notification->getStatus()->canBeSent()) {
            throw NotificationException::cannotBeSent($notification->getStatus()->value);
        }

        // Simulate sending process
        // In a real application, this would involve:
        // 1. Email service integration (SMTP, SendGrid, Mailgun, etc.)
        // 2. Template rendering and personalization
        // 3. Attachment handling
        // 4. Delivery tracking and error handling
        // 5. Retry logic for failed sends
        // 6. Compliance and unsubscribe handling
        // 7. Analytics and metrics collection
        // 8. Rate limiting and throttling
        // 9. Audit logging
        // 10. Webhook notifications

        // Add attachments if provided during sending
        if ($sendRequest && $sendRequest->hasAttachments()) {
            $this->createAttachmentsForNotification($notification, $sendRequest->attachments);
        }

        $notification->setStatus(NotificationStatus::SENT);
        $notification->setSentAt(new \DateTime());

        $this->entityManager->flush();

        return NotificationResponse::fromEntity($notification);
    }

    public function getNotificationsByUser(int $userId): array
    {
        $notifications = $this->notificationRepository->findByUser($userId);
        
        return array_map(
            fn(Notification $notification) => NotificationResponse::fromEntity($notification),
            $notifications
        );
    }

    public function getNotificationsByStatus(NotificationStatus $status): array
    {
        $notifications = $this->notificationRepository->findByStatus($status);
        
        return array_map(
            fn(Notification $notification) => NotificationResponse::fromEntity($notification),
            $notifications
        );
    }

    private function createAttachmentsForNotification(Notification $notification, array $attachments): void
    {
        foreach ($attachments as $attachmentData) {
            $attachment = new \App\Entity\NotificationAttachment();
            $attachment->setNotification($notification);
            $attachment->setFileName($attachmentData['file_name']);
            $attachment->setMimeType($attachmentData['mime_type']);
            $attachment->setFilePath($attachmentData['file_path']);

            $this->entityManager->persist($attachment);
        }
        
        $this->entityManager->flush();
    }
}
