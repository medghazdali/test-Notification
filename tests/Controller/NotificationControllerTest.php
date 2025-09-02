<?php

namespace App\Tests\Controller;

use App\Controller\NotificationController;
use App\DTO\CreateNotificationRequest;
use App\DTO\NotificationResponse;
use App\DTO\SendNotificationRequest;
use App\Service\NotificationService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class NotificationControllerTest extends TestCase
{
    private NotificationService $notificationService;
    private NotificationController $controller;

    protected function setUp(): void
    {
        $this->notificationService = $this->createMock(NotificationService::class);
        $this->controller = new NotificationController($this->notificationService);
    }

    public function testCreateWithValidData(): void
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

        $request = new Request([], [], [], [], [], [], json_encode($requestData));

        $notificationResponse = new NotificationResponse(
            id: 1,
            userId: 1,
            userName: 'John Doe',
            userEmail: 'john@example.com',
            recipientEmail: null,
            subject: 'Test Subject',
            body: 'Test Body',
            status: 'pending',
            statusLabel: 'Pending',
            createdAt: '2024-01-01 12:00:00',
            sentAt: null,
            attachmentsCount: 1,
            emailTemplateId: 1
        );

        $this->notificationService
            ->expects($this->once())
            ->method('createNotification')
            ->with($this->isInstanceOf(CreateNotificationRequest::class))
            ->willReturn($notificationResponse);

        // Act
        $response = $this->controller->create($request);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals(1, $responseData['id']);
        $this->assertEquals('Test Subject', $responseData['subject']);
        $this->assertEquals('Test Body', $responseData['body']);
        $this->assertEquals('pending', $responseData['status']);
        $this->assertEquals(1, $responseData['attachments_count']);
    }

    public function testCreateWithRecipientEmail(): void
    {
        // Arrange
        $requestData = [
            'subject' => 'Test Subject',
            'body' => 'Test Body',
            'recipient_email' => 'test@example.com'
        ];

        $request = new Request([], [], [], [], [], [], json_encode($requestData));

        $notificationResponse = new NotificationResponse(
            id: 1,
            userId: null,
            userName: null,
            userEmail: null,
            recipientEmail: 'test@example.com',
            subject: 'Test Subject',
            body: 'Test Body',
            status: 'pending',
            statusLabel: 'Pending',
            createdAt: '2024-01-01 12:00:00',
            sentAt: null,
            attachmentsCount: 0,
            emailTemplateId: null
        );

        $this->notificationService
            ->expects($this->once())
            ->method('createNotification')
            ->willReturn($notificationResponse);

        // Act
        $response = $this->controller->create($request);

        // Assert
        $this->assertEquals(201, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('test@example.com', $responseData['recipient_email']);
    }

    public function testCreateWithInvalidJson(): void
    {
        // Arrange
        $request = new Request([], [], [], [], [], [], 'invalid json');

        // Act
        $response = $this->controller->create($request);

        // Assert
        $this->assertEquals(400, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Invalid JSON', $responseData['error']);
    }

    public function testList(): void
    {
        // Arrange
        $notifications = [
            new NotificationResponse(
                id: 1,
                userId: 1,
                userName: 'John Doe',
                userEmail: 'john@example.com',
                recipientEmail: null,
                subject: 'Test Subject 1',
                body: 'Test Body 1',
                status: 'pending',
                statusLabel: 'Pending',
                createdAt: '2024-01-01 12:00:00',
                sentAt: null,
                attachmentsCount: 0,
                emailTemplateId: null
            ),
            new NotificationResponse(
                id: 2,
                userId: 2,
                userName: 'Jane Doe',
                userEmail: 'jane@example.com',
                recipientEmail: null,
                subject: 'Test Subject 2',
                body: 'Test Body 2',
                status: 'sent',
                statusLabel: 'Sent',
                createdAt: '2024-01-01 13:00:00',
                sentAt: '2024-01-01 13:05:00',
                attachmentsCount: 1,
                emailTemplateId: 1
            )
        ];

        $this->notificationService
            ->expects($this->once())
            ->method('getAllNotifications')
            ->willReturn($notifications);

        // Act
        $response = $this->controller->list();

        // Assert
        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertCount(2, $responseData);
        $this->assertEquals('Test Subject 1', $responseData[0]['subject']);
        $this->assertEquals('Test Subject 2', $responseData[1]['subject']);
    }

    public function testSend(): void
    {
        // Arrange
        $notificationId = 1;
        $requestData = [
            'attachments' => [
                [
                    'file_name' => 'invoice.pdf',
                    'mime_type' => 'application/pdf',
                    'file_path' => '/uploads/invoice.pdf'
                ]
            ]
        ];

        $request = new Request([], [], [], [], [], [], json_encode($requestData));

        $notificationResponse = new NotificationResponse(
            id: 1,
            userId: 1,
            userName: 'John Doe',
            userEmail: 'john@example.com',
            recipientEmail: null,
            subject: 'Test Subject',
            body: 'Test Body',
            status: 'sent',
            statusLabel: 'Sent',
            createdAt: '2024-01-01 12:00:00',
            sentAt: '2024-01-01 12:05:00',
            attachmentsCount: 1,
            emailTemplateId: null
        );

        $this->notificationService
            ->expects($this->once())
            ->method('sendNotification')
            ->with($notificationId, $this->isInstanceOf(SendNotificationRequest::class))
            ->willReturn($notificationResponse);

        // Act
        $response = $this->controller->send($notificationId, $request);

        // Assert
        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('sent', $responseData['status']);
        $this->assertEquals('Notification sent successfully', $responseData['message']);
        $this->assertNotNull($responseData['sent_at']);
    }

    public function testSendWithoutAttachments(): void
    {
        // Arrange
        $notificationId = 1;
        $request = new Request([], [], [], [], [], [], '{}');

        $notificationResponse = new NotificationResponse(
            id: 1,
            userId: 1,
            userName: 'John Doe',
            userEmail: 'john@example.com',
            recipientEmail: null,
            subject: 'Test Subject',
            body: 'Test Body',
            status: 'sent',
            statusLabel: 'Sent',
            createdAt: '2024-01-01 12:00:00',
            sentAt: '2024-01-01 12:05:00',
            attachmentsCount: 0,
            emailTemplateId: null
        );

        $this->notificationService
            ->expects($this->once())
            ->method('sendNotification')
            ->with($notificationId, null)
            ->willReturn($notificationResponse);

        // Act
        $response = $this->controller->send($notificationId, $request);

        // Assert
        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('sent', $responseData['status']);
    }

    public function testShow(): void
    {
        // Arrange
        $notificationId = 1;
        $notificationResponse = new NotificationResponse(
            id: 1,
            userId: 1,
            userName: 'John Doe',
            userEmail: 'john@example.com',
            recipientEmail: null,
            subject: 'Test Subject',
            body: 'Test Body',
            status: 'pending',
            statusLabel: 'Pending',
            createdAt: '2024-01-01 12:00:00',
            sentAt: null,
            attachmentsCount: 0,
            emailTemplateId: null
        );

        $this->notificationService
            ->expects($this->once())
            ->method('getNotification')
            ->with($notificationId)
            ->willReturn($notificationResponse);

        // Act
        $response = $this->controller->show($notificationId);

        // Assert
        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals(1, $responseData['id']);
        $this->assertEquals('Test Subject', $responseData['subject']);
        $this->assertEquals('John Doe', $responseData['user_name']);
    }

    public function testListPending(): void
    {
        // Arrange
        $notifications = [
            new NotificationResponse(
                id: 1,
                userId: 1,
                userName: 'John Doe',
                userEmail: 'john@example.com',
                recipientEmail: null,
                subject: 'Pending Subject 1',
                body: 'Pending Body 1',
                status: 'pending',
                statusLabel: 'Pending',
                createdAt: '2024-01-01 12:00:00',
                sentAt: null,
                attachmentsCount: 0,
                emailTemplateId: null
            ),
            new NotificationResponse(
                id: 2,
                userId: 2,
                userName: 'Jane Doe',
                userEmail: 'jane@example.com',
                recipientEmail: null,
                subject: 'Pending Subject 2',
                body: 'Pending Body 2',
                status: 'pending',
                statusLabel: 'Pending',
                createdAt: '2024-01-01 13:00:00',
                sentAt: null,
                attachmentsCount: 0,
                emailTemplateId: null
            )
        ];

        $this->notificationService
            ->expects($this->once())
            ->method('getPendingNotifications')
            ->willReturn($notifications);

        // Act
        $response = $this->controller->listPending();

        // Assert
        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertCount(2, $responseData);
        $this->assertEquals('pending', $responseData[0]['status']);
        $this->assertEquals('pending', $responseData[1]['status']);
    }
}
