<?php

namespace App\Controller;

use App\DTO\CreateNotificationRequest;
use App\DTO\NotificationResponse;
use App\DTO\SendNotificationRequest;
use App\Service\NotificationService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/notifications', name: 'api_notifications_')]
#[OA\Tag(name: 'Notifications', description: 'Notification management endpoints')]
class NotificationController extends AbstractController
{
    public function __construct(
        private NotificationService $notificationService
    ) {
    }

    #[Route('', name: 'create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/notifications',
        summary: 'Create a new notification',
        description: 'Creates a new notification with pending status. Either user_id or recipient_email must be provided.',
        tags: ['Notifications']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['subject', 'body'],
            properties: [
                new OA\Property(property: 'subject', type: 'string', maxLength: 255, description: 'Notification subject'),
                new OA\Property(property: 'body', type: 'string', description: 'Notification body content'),
                new OA\Property(property: 'user_id', type: 'integer', description: 'ID of the user (optional)'),
                new OA\Property(property: 'recipient_email', type: 'string', format: 'email', description: 'Recipient email address (optional)'),
                new OA\Property(property: 'email_template_id', type: 'integer', description: 'ID of the email template to link with this notification (optional). Links notification to a pre-defined template for consistent branding and template usage tracking.'),
                new OA\Property(
                    property: 'attachments',
                    type: 'array',
                    description: 'Array of attachments (optional)',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'file_name', type: 'string', description: 'Original file name'),
                            new OA\Property(property: 'mime_type', type: 'string', description: 'File MIME type'),
                            new OA\Property(property: 'file_path', type: 'string', description: 'Storage path of the file')
                        ]
                    )
                )
            ],
            example: [
                'subject' => 'Welcome to Our Service',
                'body' => 'Thank you for joining our service!',
                'user_id' => 1,
                'email_template_id' => 1,
                'attachments' => [
                    [
                        'file_name' => 'welcome_guide.pdf',
                        'mime_type' => 'application/pdf',
                        'file_path' => '/uploads/attachments/welcome_guide_123.pdf'
                    ]
                ]
            ]
        )
    )]

    #[OA\Response(
        response: 201,
        description: 'Notification created successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'user_id', type: 'integer', nullable: true),
                new OA\Property(property: 'user_name', type: 'string', nullable: true),
                new OA\Property(property: 'user_email', type: 'string', nullable: true),
                new OA\Property(property: 'recipient_email', type: 'string', nullable: true),
                new OA\Property(property: 'subject', type: 'string'),
                new OA\Property(property: 'body', type: 'string'),
                new OA\Property(property: 'status', type: 'string', enum: ['pending', 'sent', 'failed', 'delivered', 'archived']),
                new OA\Property(property: 'status_label', type: 'string'),
                new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                new OA\Property(property: 'sent_at', type: 'string', format: 'date-time', nullable: true),
                new OA\Property(property: 'attachments_count', type: 'integer'),
                new OA\Property(property: 'email_template_id', type: 'integer', nullable: true)
            ]
        )
    )]
    #[OA\Response(response: 400, description: 'Bad request - validation failed')]
    #[OA\Response(response: 404, description: 'User or email template not found')]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!$data) {
            return new JsonResponse(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        $createRequest = new CreateNotificationRequest($data);
        $notificationResponse = $this->notificationService->createNotification($createRequest);

        return new JsonResponse($notificationResponse->toArray(), Response::HTTP_CREATED);
    }

    #[Route('', name: 'list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/notifications',
        summary: 'List all notifications',
        description: 'Returns all notifications in the system with their current status and details.',
        tags: ['Notifications']
    )]
    #[OA\Response(
        response: 200,
        description: 'List of all notifications',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'id', type: 'integer'),
                    new OA\Property(property: 'user_id', type: 'integer', nullable: true),
                    new OA\Property(property: 'user_name', type: 'string', nullable: true),
                    new OA\Property(property: 'user_email', type: 'string', nullable: true),
                    new OA\Property(property: 'recipient_email', type: 'string', nullable: true),
                    new OA\Property(property: 'subject', type: 'string'),
                    new OA\Property(property: 'body', type: 'string'),
                    new OA\Property(property: 'status', type: 'string', enum: ['pending', 'sent', 'failed', 'delivered', 'archived']),
                    new OA\Property(property: 'status_label', type: 'string'),
                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                    new OA\Property(property: 'sent_at', type: 'string', format: 'date-time', nullable: true),
                    new OA\Property(property: 'attachments_count', type: 'integer'),
                    new OA\Property(property: 'email_template_id', type: 'integer', nullable: true)
                ]
            )
        )
    )]
    public function list(): JsonResponse
    {
        $notifications = $this->notificationService->getAllNotifications();
        $responseData = array_map(fn(NotificationResponse $response) => $response->toArray(), $notifications);

        return new JsonResponse($responseData, Response::HTTP_OK);
    }

    #[Route('/{id}/send', name: 'send', methods: ['POST'])]
    #[OA\Post(
        path: '/api/notifications/{id}/send',
        summary: 'Send a notification',
        description: 'Simulates sending a notification. Changes status from pending to sent and sets sent_at timestamp. Can optionally add attachments during sending.',
        tags: ['Notifications']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'Notification ID',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\RequestBody(
        required: false,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'attachments',
                    type: 'array',
                    description: 'Array of attachments to add during sending (optional)',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'file_name', type: 'string', description: 'Original file name'),
                            new OA\Property(property: 'mime_type', type: 'string', description: 'File MIME type'),
                            new OA\Property(property: 'file_path', type: 'string', description: 'Storage path of the file')
                        ]
                    )
                )
            ],
            example: [
                'attachments' => [
                    [
                        'file_name' => 'invoice.pdf',
                        'mime_type' => 'application/pdf',
                        'file_path' => '/uploads/attachments/invoice_456.pdf'
                    ]
                ]
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Notification sent successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'user_id', type: 'integer', nullable: true),
                new OA\Property(property: 'user_name', type: 'string', nullable: true),
                new OA\Property(property: 'user_email', type: 'string', nullable: true),
                new OA\Property(property: 'recipient_email', type: 'string', nullable: true),
                new OA\Property(property: 'subject', type: 'string'),
                new OA\Property(property: 'body', type: 'string'),
                new OA\Property(property: 'status', type: 'string', enum: ['sent']),
                new OA\Property(property: 'status_label', type: 'string'),
                new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                new OA\Property(property: 'sent_at', type: 'string', format: 'date-time'),
                new OA\Property(property: 'attachments_count', type: 'integer'),
                new OA\Property(property: 'email_template_id', type: 'integer', nullable: true),
                new OA\Property(property: 'message', type: 'string')
            ]
        )
    )]
    #[OA\Response(response: 400, description: 'Bad request - notification cannot be sent')]
    #[OA\Response(response: 404, description: 'Notification not found')]
    public function send(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $sendRequest = $data ? new SendNotificationRequest($data) : null;
        
        $notificationResponse = $this->notificationService->sendNotification($id, $sendRequest);
        $responseData = $notificationResponse->toArray();
        $responseData['message'] = 'Notification sent successfully';

        return new JsonResponse($responseData, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    #[OA\Get(
        path: '/api/notifications/{id}',
        summary: 'Get notification details',
        description: 'Returns detailed information about a specific notification.',
        tags: ['Notifications']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'Notification ID',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Notification details',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'user_id', type: 'integer', nullable: true),
                new OA\Property(property: 'user_name', type: 'string', nullable: true),
                new OA\Property(property: 'user_email', type: 'string', nullable: true),
                new OA\Property(property: 'recipient_email', type: 'string', nullable: true),
                new OA\Property(property: 'subject', type: 'string'),
                new OA\Property(property: 'body', type: 'string'),
                new OA\Property(property: 'status', type: 'string', enum: ['pending', 'sent', 'failed', 'delivered', 'archived']),
                new OA\Property(property: 'status_label', type: 'string'),
                new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                new OA\Property(property: 'sent_at', type: 'string', format: 'date-time', nullable: true),
                new OA\Property(property: 'attachments_count', type: 'integer'),
                new OA\Property(property: 'email_template_id', type: 'integer', nullable: true)
            ]
        )
    )]
    #[OA\Response(response: 404, description: 'Notification not found')]
    public function show(int $id): JsonResponse
    {
        $notificationResponse = $this->notificationService->getNotification($id);

        return new JsonResponse($notificationResponse->toArray(), Response::HTTP_OK);
    }

    #[Route('/pending', name: 'list_pending', methods: ['GET'])]
    #[OA\Get(
        path: '/api/notifications/pending',
        summary: 'List pending notifications',
        description: 'Returns all notifications with pending status that are waiting to be sent.',
        tags: ['Notifications']
    )]
    #[OA\Response(
        response: 200,
        description: 'List of pending notifications',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'id', type: 'integer'),
                    new OA\Property(property: 'user_id', type: 'integer', nullable: true),
                    new OA\Property(property: 'user_name', type: 'string', nullable: true),
                    new OA\Property(property: 'user_email', type: 'string', nullable: true),
                    new OA\Property(property: 'recipient_email', type: 'string', nullable: true),
                    new OA\Property(property: 'subject', type: 'string'),
                    new OA\Property(property: 'body', type: 'string'),
                    new OA\Property(property: 'status', type: 'string', enum: ['pending']),
                    new OA\Property(property: 'status_label', type: 'string'),
                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                    new OA\Property(property: 'sent_at', type: 'string', format: 'date-time', nullable: true),
                    new OA\Property(property: 'attachments_count', type: 'integer'),
                    new OA\Property(property: 'email_template_id', type: 'integer', nullable: true)
                ]
            )
        )
    )]
    public function listPending(): JsonResponse
    {
        $notifications = $this->notificationService->getPendingNotifications();
        $responseData = array_map(fn(NotificationResponse $response) => $response->toArray(), $notifications);

        return new JsonResponse($responseData, Response::HTTP_OK);
    }
}
