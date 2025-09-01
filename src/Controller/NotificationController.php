<?php

namespace App\Controller;

use App\DTO\CreateNotificationRequest;
use App\DTO\NotificationResponse;
use App\Service\NotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/notifications', name: 'api_notifications_')]
class NotificationController extends AbstractController
{
    public function __construct(
        private NotificationService $notificationService
    ) {
    }

    #[Route('', name: 'create', methods: ['POST'])]
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
    public function list(): JsonResponse
    {
        $notifications = $this->notificationService->getAllNotifications();
        $responseData = array_map(fn(NotificationResponse $response) => $response->toArray(), $notifications);

        return new JsonResponse($responseData, Response::HTTP_OK);
    }

    #[Route('/{id}/send', name: 'send', methods: ['POST'])]
    public function send(int $id): JsonResponse
    {
        $notificationResponse = $this->notificationService->sendNotification($id);
        $responseData = $notificationResponse->toArray();
        $responseData['message'] = 'Notification sent successfully';

        return new JsonResponse($responseData, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $notificationResponse = $this->notificationService->getNotification($id);

        return new JsonResponse($notificationResponse->toArray(), Response::HTTP_OK);
    }

    #[Route('/pending', name: 'list_pending', methods: ['GET'])]
    public function listPending(): JsonResponse
    {
        $notifications = $this->notificationService->getPendingNotifications();
        $responseData = array_map(fn(NotificationResponse $response) => $response->toArray(), $notifications);

        return new JsonResponse($responseData, Response::HTTP_OK);
    }
}
