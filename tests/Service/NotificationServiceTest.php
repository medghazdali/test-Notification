<?php

namespace App\Tests\Service;

use App\DTO\CreateNotificationRequest;
use App\DTO\NotificationResponse;
use App\DTO\SendNotificationRequest;
use App\Entity\EmailTemplate;
use App\Entity\Notification;
use App\Entity\NotificationAttachment;
use App\Entity\User;
use App\Enum\NotificationStatus;
use App\Exception\NotificationException;
use App\Repository\EmailTemplateRepository;
use App\Repository\NotificationAttachmentRepository;
use App\Repository\NotificationRepository;
use App\Repository\UserRepository;
use App\Service\NotificationService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class NotificationServiceTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private ValidatorInterface $validator;
    private NotificationRepository $notificationRepository;
    private UserRepository $userRepository;
    private EmailTemplateRepository $emailTemplateRepository;
    private NotificationAttachmentRepository $attachmentRepository;
    private NotificationService $service;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->notificationRepository = $this->createMock(NotificationRepository::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->emailTemplateRepository = $this->createMock(EmailTemplateRepository::class);
        $this->attachmentRepository = $this->createMock(NotificationAttachmentRepository::class);

        $this->service = new NotificationService(
            $this->entityManager,
            $this->validator,
            $this->notificationRepository,
            $this->userRepository,
            $this->emailTemplateRepository,
            $this->attachmentRepository
        );
    }

    public function testCreateNotificationWithValidData(): void
    {
        // Arrange
        $requestData = [
            'subject' => 'Test Subject',
            'body' => 'Test Body',
            'user_id' => 1,
            'email_template_id' => 1,
            'attachments' => [
                [
                    'file_name' => 'test.pdf',
                    'mime_type' => 'application/pdf',
                    'file_path' => '/uploads/test.pdf'
                ]
            ]
        ];

        $request = new CreateNotificationRequest($requestData);

        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);
        $user->method('getFirstName')->willReturn('John');
        $user->method('getLastName')->willReturn('Doe');
        $user->method('getEmail')->willReturn('john@example.com');

        $emailTemplate = $this->createMock(EmailTemplate::class);
        $emailTemplate->method('getId')->willReturn(1);

        $this->validator
            ->expects($this->exactly(2))
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->userRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($user);

        $this->emailTemplateRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($emailTemplate);

        $this->entityManager
            ->expects($this->exactly(2))
            ->method('persist')
            ->with($this->callback(function ($entity) {
                if ($entity instanceof Notification) {
                    // Simulate the ID being set after persist
                    $reflection = new \ReflectionClass($entity);
                    $idProperty = $reflection->getProperty('id');
                    $idProperty->setAccessible(true);
                    $idProperty->setValue($entity, 1);
                }
                return true;
            }));

        $this->entityManager
            ->expects($this->exactly(2))
            ->method('flush');

        // Act
        $response = $this->service->createNotification($request);

        // Assert
        $this->assertInstanceOf(NotificationResponse::class, $response);
        $this->assertEquals('Test Subject', $response->subject);
        $this->assertEquals('Test Body', $response->body);
        $this->assertEquals('pending', $response->status);
        $this->assertEquals(1, $response->userId);
        $this->assertEquals('John Doe', $response->userName);
        $this->assertEquals('john@example.com', $response->userEmail);
    }

    public function testCreateNotificationWithRecipientEmail(): void
    {
        // Arrange
        $requestData = [
            'subject' => 'Test Subject',
            'body' => 'Test Body',
            'recipient_email' => 'test@example.com'
        ];

        $request = new CreateNotificationRequest($requestData);

        $this->validator
            ->expects($this->exactly(2))
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->callback(function ($entity) {
                if ($entity instanceof Notification) {
                    // Simulate the ID being set after persist
                    $reflection = new \ReflectionClass($entity);
                    $idProperty = $reflection->getProperty('id');
                    $idProperty->setAccessible(true);
                    $idProperty->setValue($entity, 1);
                }
                return true;
            }));

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        // Act
        $response = $this->service->createNotification($request);

        // Assert
        $this->assertInstanceOf(NotificationResponse::class, $response);
        $this->assertEquals('test@example.com', $response->recipientEmail);
        $this->assertEquals('Test Subject', $response->subject);
        $this->assertEquals('Test Body', $response->body);
        $this->assertEquals('pending', $response->status);
    }

    public function testCreateNotificationWithValidationErrors(): void
    {
        // Arrange
        $requestData = [
            'subject' => '',
            'body' => 'Test Body',
            'user_id' => 1
        ];

        $request = new CreateNotificationRequest($requestData);

        $violation = $this->createMock(ConstraintViolation::class);
        $violation->method('getMessage')->willReturn('Subject is required');

        $violations = new ConstraintViolationList([$violation]);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with($request)
            ->willReturn($violations);

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Validation failed: Subject is required');

        $this->service->createNotification($request);
    }

    public function testCreateNotificationWithoutRecipient(): void
    {
        // Arrange
        $requestData = [
            'subject' => 'Test Subject',
            'body' => 'Test Body'
        ];

        $request = new CreateNotificationRequest($requestData);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        // Act & Assert
        $this->expectException(NotificationException::class);
        $this->expectExceptionMessage('Either user_id or recipient_email must be provided');

        $this->service->createNotification($request);
    }

    public function testCreateNotificationWithUserNotFound(): void
    {
        // Arrange
        $requestData = [
            'subject' => 'Test Subject',
            'body' => 'Test Body',
            'user_id' => 999
        ];

        $request = new CreateNotificationRequest($requestData);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->userRepository
            ->expects($this->once())
            ->method('find')
            ->with(999)
            ->willReturn(null);

        // Act & Assert
        $this->expectException(NotificationException::class);
        $this->expectExceptionMessage('User with ID 999 not found');

        $this->service->createNotification($request);
    }

    public function testCreateNotificationWithEmailTemplateNotFound(): void
    {
        // Arrange
        $requestData = [
            'subject' => 'Test Subject',
            'body' => 'Test Body',
            'user_id' => 1,
            'email_template_id' => 999
        ];

        $request = new CreateNotificationRequest($requestData);

        $user = $this->createMock(User::class);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->userRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($user);

        $this->emailTemplateRepository
            ->expects($this->once())
            ->method('find')
            ->with(999)
            ->willReturn(null);

        // Act & Assert
        $this->expectException(NotificationException::class);
        $this->expectExceptionMessage('Email template with ID 999 not found');

        $this->service->createNotification($request);
    }

    public function testGetNotification(): void
    {
        // Arrange
        $notificationId = 1;
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);
        $user->method('getFirstName')->willReturn('John');
        $user->method('getLastName')->willReturn('Doe');
        $user->method('getEmail')->willReturn('john@example.com');

        $notification = $this->createMock(Notification::class);
        $notification->method('getId')->willReturn(1);
        $notification->method('getUser')->willReturn($user);
        $notification->method('getRecipientEmail')->willReturn(null);
        $notification->method('getSubject')->willReturn('Test Subject');
        $notification->method('getBody')->willReturn('Test Body');
        $notification->method('getStatus')->willReturn(NotificationStatus::PENDING);
        $notification->method('getCreatedAt')->willReturn(new \DateTime('2024-01-01 12:00:00'));
        $notification->method('getSentAt')->willReturn(null);
        $notification->method('getAttachments')->willReturn(new ArrayCollection());
        $notification->method('getEmailTemplate')->willReturn(null);

        $this->notificationRepository
            ->expects($this->once())
            ->method('find')
            ->with($notificationId)
            ->willReturn($notification);

        // Act
        $response = $this->service->getNotification($notificationId);

        // Assert
        $this->assertInstanceOf(NotificationResponse::class, $response);
        $this->assertEquals(1, $response->id);
        $this->assertEquals('Test Subject', $response->subject);
        $this->assertEquals('John Doe', $response->userName);
    }

    public function testGetNotificationNotFound(): void
    {
        // Arrange
        $notificationId = 999;

        $this->notificationRepository
            ->expects($this->once())
            ->method('find')
            ->with($notificationId)
            ->willReturn(null);

        // Act & Assert
        $this->expectException(NotificationException::class);
        $this->expectExceptionMessage('Notification with ID 999 not found');

        $this->service->getNotification($notificationId);
    }

    public function testGetAllNotifications(): void
    {
        // Arrange
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);
        $user->method('getFirstName')->willReturn('John');
        $user->method('getLastName')->willReturn('Doe');
        $user->method('getEmail')->willReturn('john@example.com');

        $notification1 = $this->createMock(Notification::class);
        $notification1->method('getId')->willReturn(1);
        $notification1->method('getUser')->willReturn($user);
        $notification1->method('getRecipientEmail')->willReturn(null);
        $notification1->method('getSubject')->willReturn('Subject 1');
        $notification1->method('getBody')->willReturn('Body 1');
        $notification1->method('getStatus')->willReturn(NotificationStatus::PENDING);
        $notification1->method('getCreatedAt')->willReturn(new \DateTime('2024-01-01 12:00:00'));
        $notification1->method('getSentAt')->willReturn(null);
        $notification1->method('getAttachments')->willReturn(new ArrayCollection());
        $notification1->method('getEmailTemplate')->willReturn(null);

        $notification2 = $this->createMock(Notification::class);
        $notification2->method('getId')->willReturn(2);
        $notification2->method('getUser')->willReturn($user);
        $notification2->method('getRecipientEmail')->willReturn(null);
        $notification2->method('getSubject')->willReturn('Subject 2');
        $notification2->method('getBody')->willReturn('Body 2');
        $notification2->method('getStatus')->willReturn(NotificationStatus::SENT);
        $notification2->method('getCreatedAt')->willReturn(new \DateTime('2024-01-01 13:00:00'));
        $notification2->method('getSentAt')->willReturn(new \DateTime('2024-01-01 13:05:00'));
        $notification2->method('getAttachments')->willReturn(new ArrayCollection());
        $notification2->method('getEmailTemplate')->willReturn(null);

        $notifications = [$notification1, $notification2];

        $this->notificationRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($notifications);

        // Act
        $responses = $this->service->getAllNotifications();

        // Assert
        $this->assertCount(2, $responses);
        $this->assertInstanceOf(NotificationResponse::class, $responses[0]);
        $this->assertInstanceOf(NotificationResponse::class, $responses[1]);
        $this->assertEquals('Subject 1', $responses[0]->subject);
        $this->assertEquals('Subject 2', $responses[1]->subject);
    }

    public function testGetPendingNotifications(): void
    {
        // Arrange
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);
        $user->method('getFirstName')->willReturn('John');
        $user->method('getLastName')->willReturn('Doe');
        $user->method('getEmail')->willReturn('john@example.com');

        $notification = $this->createMock(Notification::class);
        $notification->method('getId')->willReturn(1);
        $notification->method('getUser')->willReturn($user);
        $notification->method('getRecipientEmail')->willReturn(null);
        $notification->method('getSubject')->willReturn('Pending Subject');
        $notification->method('getBody')->willReturn('Pending Body');
        $notification->method('getStatus')->willReturn(NotificationStatus::PENDING);
        $notification->method('getCreatedAt')->willReturn(new \DateTime('2024-01-01 12:00:00'));
        $notification->method('getSentAt')->willReturn(null);
        $notification->method('getAttachments')->willReturn(new ArrayCollection());
        $notification->method('getEmailTemplate')->willReturn(null);

        $notifications = [$notification];

        $this->notificationRepository
            ->expects($this->once())
            ->method('findPendingNotifications')
            ->willReturn($notifications);

        // Act
        $responses = $this->service->getPendingNotifications();

        // Assert
        $this->assertCount(1, $responses);
        $this->assertInstanceOf(NotificationResponse::class, $responses[0]);
        $this->assertEquals('pending', $responses[0]->status);
    }

    public function testSendNotification(): void
    {
        // Arrange
        $notificationId = 1;
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);
        $user->method('getFirstName')->willReturn('John');
        $user->method('getLastName')->willReturn('Doe');
        $user->method('getEmail')->willReturn('john@example.com');

        $notification = $this->createMock(Notification::class);
        $notification->method('getId')->willReturn(1);
        $notification->method('getUser')->willReturn($user);
        $notification->method('getRecipientEmail')->willReturn(null);
        $notification->method('getSubject')->willReturn('Test Subject');
        $notification->method('getBody')->willReturn('Test Body');
        $notification->method('getStatus')->willReturn(NotificationStatus::PENDING);
        $notification->method('getCreatedAt')->willReturn(new \DateTime('2024-01-01 12:00:00'));
        $notification->method('getSentAt')->willReturn(null);
        $notification->method('getAttachments')->willReturn(new ArrayCollection());
        $notification->method('getEmailTemplate')->willReturn(null);

        $this->notificationRepository
            ->expects($this->once())
            ->method('find')
            ->with($notificationId)
            ->willReturn($notification);

        $notification->expects($this->once())
            ->method('setStatus')
            ->with(NotificationStatus::SENT);

        $notification->expects($this->once())
            ->method('setSentAt')
            ->with($this->isInstanceOf(\DateTime::class));

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        // Act
        $response = $this->service->sendNotification($notificationId);

        // Assert
        $this->assertInstanceOf(NotificationResponse::class, $response);
        $this->assertEquals(1, $response->id);
    }

    public function testSendNotificationWithAttachments(): void
    {
        // Arrange
        $notificationId = 1;
        $sendRequest = new SendNotificationRequest([
            'attachments' => [
                [
                    'file_name' => 'invoice.pdf',
                    'mime_type' => 'application/pdf',
                    'file_path' => '/uploads/invoice.pdf'
                ]
            ]
        ]);

        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);
        $user->method('getFirstName')->willReturn('John');
        $user->method('getLastName')->willReturn('Doe');
        $user->method('getEmail')->willReturn('john@example.com');

        $notification = $this->createMock(Notification::class);
        $notification->method('getId')->willReturn(1);
        $notification->method('getUser')->willReturn($user);
        $notification->method('getRecipientEmail')->willReturn(null);
        $notification->method('getSubject')->willReturn('Test Subject');
        $notification->method('getBody')->willReturn('Test Body');
        $notification->method('getStatus')->willReturn(NotificationStatus::PENDING);
        $notification->method('getCreatedAt')->willReturn(new \DateTime('2024-01-01 12:00:00'));
        $notification->method('getSentAt')->willReturn(null);
        $notification->method('getAttachments')->willReturn(new ArrayCollection());
        $notification->method('getEmailTemplate')->willReturn(null);

        $this->notificationRepository
            ->expects($this->once())
            ->method('find')
            ->with($notificationId)
            ->willReturn($notification);

        $notification->expects($this->once())
            ->method('setStatus')
            ->with(NotificationStatus::SENT);

        $notification->expects($this->once())
            ->method('setSentAt')
            ->with($this->isInstanceOf(\DateTime::class));

        // Expect persist to be called once for the attachment
        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(\App\Entity\NotificationAttachment::class));

        $this->entityManager
            ->expects($this->exactly(2))
            ->method('flush');

        // Act
        $response = $this->service->sendNotification($notificationId, $sendRequest);

        // Assert
        $this->assertInstanceOf(NotificationResponse::class, $response);
    }

    public function testSendNotificationNotFound(): void
    {
        // Arrange
        $notificationId = 999;

        $this->notificationRepository
            ->expects($this->once())
            ->method('find')
            ->with($notificationId)
            ->willReturn(null);

        // Act & Assert
        $this->expectException(NotificationException::class);
        $this->expectExceptionMessage('Notification with ID 999 not found');

        $this->service->sendNotification($notificationId);
    }

    public function testSendNotificationCannotBeSent(): void
    {
        // Arrange
        $notificationId = 1;
        $user = $this->createMock(User::class);

        $notification = $this->createMock(Notification::class);
        $notification->method('getStatus')->willReturn(NotificationStatus::SENT);

        $this->notificationRepository
            ->expects($this->once())
            ->method('find')
            ->with($notificationId)
            ->willReturn($notification);

        // Act & Assert
        $this->expectException(NotificationException::class);
        $this->expectExceptionMessage('Notification cannot be sent. Current status: sent');

        $this->service->sendNotification($notificationId);
    }

    public function testGetNotificationsByUser(): void
    {
        // Arrange
        $userId = 1;
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);
        $user->method('getFirstName')->willReturn('John');
        $user->method('getLastName')->willReturn('Doe');
        $user->method('getEmail')->willReturn('john@example.com');

        $notification = $this->createMock(Notification::class);
        $notification->method('getId')->willReturn(1);
        $notification->method('getUser')->willReturn($user);
        $notification->method('getRecipientEmail')->willReturn(null);
        $notification->method('getSubject')->willReturn('User Subject');
        $notification->method('getBody')->willReturn('User Body');
        $notification->method('getStatus')->willReturn(NotificationStatus::PENDING);
        $notification->method('getCreatedAt')->willReturn(new \DateTime('2024-01-01 12:00:00'));
        $notification->method('getSentAt')->willReturn(null);
        $notification->method('getAttachments')->willReturn(new ArrayCollection());
        $notification->method('getEmailTemplate')->willReturn(null);

        $notifications = [$notification];

        $this->notificationRepository
            ->expects($this->once())
            ->method('findByUser')
            ->with($userId)
            ->willReturn($notifications);

        // Act
        $responses = $this->service->getNotificationsByUser($userId);

        // Assert
        $this->assertCount(1, $responses);
        $this->assertInstanceOf(NotificationResponse::class, $responses[0]);
        $this->assertEquals(1, $responses[0]->userId);
    }

    public function testGetNotificationsByStatus(): void
    {
        // Arrange
        $status = NotificationStatus::SENT;
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);
        $user->method('getFirstName')->willReturn('John');
        $user->method('getLastName')->willReturn('Doe');
        $user->method('getEmail')->willReturn('john@example.com');

        $notification = $this->createMock(Notification::class);
        $notification->method('getId')->willReturn(1);
        $notification->method('getUser')->willReturn($user);
        $notification->method('getRecipientEmail')->willReturn(null);
        $notification->method('getSubject')->willReturn('Sent Subject');
        $notification->method('getBody')->willReturn('Sent Body');
        $notification->method('getStatus')->willReturn(NotificationStatus::SENT);
        $notification->method('getCreatedAt')->willReturn(new \DateTime('2024-01-01 12:00:00'));
        $notification->method('getSentAt')->willReturn(new \DateTime('2024-01-01 12:05:00'));
        $notification->method('getAttachments')->willReturn(new ArrayCollection());
        $notification->method('getEmailTemplate')->willReturn(null);

        $notifications = [$notification];

        $this->notificationRepository
            ->expects($this->once())
            ->method('findByStatus')
            ->with($status)
            ->willReturn($notifications);

        // Act
        $responses = $this->service->getNotificationsByStatus($status);

        // Assert
        $this->assertCount(1, $responses);
        $this->assertInstanceOf(NotificationResponse::class, $responses[0]);
        $this->assertEquals('sent', $responses[0]->status);
    }
}
