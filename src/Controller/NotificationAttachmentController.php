<?php

namespace App\Controller;

use App\DTO\CreateNotificationAttachmentRequest;
use App\DTO\NotificationAttachmentResponse;
use App\Service\NotificationAttachmentService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/notification-attachments', name: 'api_notification_attachments_')]
#[OA\Tag(name: 'Notification Attachments', description: 'Notification attachment management endpoints')]
class NotificationAttachmentController extends AbstractController
{
    public function __construct(
        private NotificationAttachmentService $attachmentService
    ) {
    }

    #[Route('', name: 'create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/notification-attachments',
        summary: 'Create a new notification attachment',
        description: 'Creates a new attachment for a specific notification.',
        tags: ['Notification Attachments']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['notification_id', 'file_name', 'mime_type', 'file_path'],
            properties: [
                new OA\Property(property: 'notification_id', type: 'integer', description: 'ID of the notification'),
                new OA\Property(property: 'file_name', type: 'string', maxLength: 255, description: 'Original file name'),
                new OA\Property(property: 'mime_type', type: 'string', maxLength: 100, description: 'File MIME type'),
                new OA\Property(property: 'file_path', type: 'string', maxLength: 500, description: 'Storage path of the file')
            ],
            example: [
                'notification_id' => 1,
                'file_name' => 'document.pdf',
                'mime_type' => 'application/pdf',
                'file_path' => '/uploads/attachments/document_123.pdf'
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Notification attachment created successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'notification_id', type: 'integer'),
                new OA\Property(property: 'notification_subject', type: 'string'),
                new OA\Property(property: 'file_name', type: 'string'),
                new OA\Property(property: 'mime_type', type: 'string'),
                new OA\Property(property: 'file_path', type: 'string'),
                new OA\Property(property: 'created_at', type: 'string', format: 'date-time')
            ]
        )
    )]
    #[OA\Response(response: 400, description: 'Bad request - validation failed')]
    #[OA\Response(response: 404, description: 'Notification not found')]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!$data) {
            return new JsonResponse(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        $createRequest = new CreateNotificationAttachmentRequest($data);
        $attachmentResponse = $this->attachmentService->createAttachment($createRequest);

        return new JsonResponse($attachmentResponse->toArray(), Response::HTTP_CREATED);
    }

    #[Route('', name: 'list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/notification-attachments',
        summary: 'List all notification attachments',
        description: 'Returns all notification attachments in the system.',
        tags: ['Notification Attachments']
    )]
    #[OA\Response(
        response: 200,
        description: 'List of all notification attachments',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'id', type: 'integer'),
                    new OA\Property(property: 'notification_id', type: 'integer'),
                    new OA\Property(property: 'notification_subject', type: 'string'),
                    new OA\Property(property: 'file_name', type: 'string'),
                    new OA\Property(property: 'mime_type', type: 'string'),
                    new OA\Property(property: 'file_path', type: 'string'),
                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time')
                ]
            )
        )
    )]
    public function list(): JsonResponse
    {
        $attachments = $this->attachmentService->getAllAttachments();
        $responseData = array_map(fn(NotificationAttachmentResponse $response) => $response->toArray(), $attachments);

        return new JsonResponse($responseData, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    #[OA\Get(
        path: '/api/notification-attachments/{id}',
        summary: 'Get notification attachment details',
        description: 'Returns detailed information about a specific notification attachment.',
        tags: ['Notification Attachments']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'Notification attachment ID',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Notification attachment details',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'notification_id', type: 'integer'),
                new OA\Property(property: 'notification_subject', type: 'string'),
                new OA\Property(property: 'file_name', type: 'string'),
                new OA\Property(property: 'mime_type', type: 'string'),
                new OA\Property(property: 'file_path', type: 'string'),
                new OA\Property(property: 'created_at', type: 'string', format: 'date-time')
            ]
        )
    )]
    #[OA\Response(response: 404, description: 'Notification attachment not found')]
    public function show(int $id): JsonResponse
    {
        $attachmentResponse = $this->attachmentService->getAttachment($id);

        return new JsonResponse($attachmentResponse->toArray(), Response::HTTP_OK);
    }

    #[Route('/notification/{notificationId}', name: 'list_by_notification', methods: ['GET'])]
    #[OA\Get(
        path: '/api/notification-attachments/notification/{notificationId}',
        summary: 'List attachments for a notification',
        description: 'Returns all attachments for a specific notification.',
        tags: ['Notification Attachments']
    )]
    #[OA\Parameter(
        name: 'notificationId',
        in: 'path',
        description: 'Notification ID',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'List of attachments for the notification',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'id', type: 'integer'),
                    new OA\Property(property: 'notification_id', type: 'integer'),
                    new OA\Property(property: 'notification_subject', type: 'string'),
                    new OA\Property(property: 'file_name', type: 'string'),
                    new OA\Property(property: 'mime_type', type: 'string'),
                    new OA\Property(property: 'file_path', type: 'string'),
                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time')
                ]
            )
        )
    )]
    #[OA\Response(response: 404, description: 'Notification not found')]
    public function listByNotification(int $notificationId): JsonResponse
    {
        $attachments = $this->attachmentService->getAttachmentsByNotification($notificationId);
        $responseData = array_map(fn(NotificationAttachmentResponse $response) => $response->toArray(), $attachments);

        return new JsonResponse($responseData, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/notification-attachments/{id}',
        summary: 'Update a notification attachment',
        description: 'Updates an existing notification attachment.',
        tags: ['Notification Attachments']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'Notification attachment ID',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['notification_id', 'file_name', 'mime_type', 'file_path'],
            properties: [
                new OA\Property(property: 'notification_id', type: 'integer', description: 'ID of the notification'),
                new OA\Property(property: 'file_name', type: 'string', maxLength: 255, description: 'Original file name'),
                new OA\Property(property: 'mime_type', type: 'string', maxLength: 100, description: 'File MIME type'),
                new OA\Property(property: 'file_path', type: 'string', maxLength: 500, description: 'Storage path of the file')
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Notification attachment updated successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'notification_id', type: 'integer'),
                new OA\Property(property: 'notification_subject', type: 'string'),
                new OA\Property(property: 'file_name', type: 'string'),
                new OA\Property(property: 'mime_type', type: 'string'),
                new OA\Property(property: 'file_path', type: 'string'),
                new OA\Property(property: 'created_at', type: 'string', format: 'date-time')
            ]
        )
    )]
    #[OA\Response(response: 400, description: 'Bad request - validation failed')]
    #[OA\Response(response: 404, description: 'Notification attachment or notification not found')]
    public function update(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!$data) {
            return new JsonResponse(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        $updateRequest = new CreateNotificationAttachmentRequest($data);
        $attachmentResponse = $this->attachmentService->updateAttachment($id, $updateRequest);

        return new JsonResponse($attachmentResponse->toArray(), Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/notification-attachments/{id}',
        summary: 'Delete a notification attachment',
        description: 'Deletes a notification attachment.',
        tags: ['Notification Attachments']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'Notification attachment ID',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 204,
        description: 'Notification attachment deleted successfully'
    )]
    #[OA\Response(response: 404, description: 'Notification attachment not found')]
    public function delete(int $id): JsonResponse
    {
        $this->attachmentService->deleteAttachment($id);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
