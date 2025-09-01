<?php

namespace App\Controller;

use App\DTO\CreateUserRequest;
use App\DTO\UserResponse;
use App\Service\UserService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/users', name: 'api_users_')]
#[OA\Tag(name: 'Users', description: 'User management endpoints')]
class UserController extends AbstractController
{
    public function __construct(
        private UserService $userService
    ) {
    }

    #[Route('', name: 'create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/users',
        summary: 'Create a new user',
        description: 'Creates a new user with required email, first name, and last name.',
        tags: ['Users']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['email', 'first_name', 'last_name'],
            properties: [
                new OA\Property(property: 'email', type: 'string', format: 'email', maxLength: 255, description: 'User email address'),
                new OA\Property(property: 'first_name', type: 'string', maxLength: 255, description: 'User first name'),
                new OA\Property(property: 'last_name', type: 'string', maxLength: 255, description: 'User last name')
            ],
            example: [
                'email' => 'john.doe@example.com',
                'first_name' => 'John',
                'last_name' => 'Doe'
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'User created successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'email', type: 'string'),
                new OA\Property(property: 'first_name', type: 'string'),
                new OA\Property(property: 'last_name', type: 'string'),
                new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                new OA\Property(property: 'notifications_count', type: 'integer')
            ]
        )
    )]
    #[OA\Response(response: 400, description: 'Bad request - validation failed')]
    #[OA\Response(response: 409, description: 'Conflict - user with email already exists')]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!$data) {
            return new JsonResponse(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        $createRequest = new CreateUserRequest($data);
        $userResponse = $this->userService->createUser($createRequest);

        return new JsonResponse($userResponse->toArray(), Response::HTTP_CREATED);
    }

    #[Route('', name: 'list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/users',
        summary: 'List all users',
        description: 'Returns all users in the system with their notification counts.',
        tags: ['Users']
    )]
    #[OA\Response(
        response: 200,
        description: 'List of all users',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'id', type: 'integer'),
                    new OA\Property(property: 'email', type: 'string'),
                    new OA\Property(property: 'first_name', type: 'string'),
                    new OA\Property(property: 'last_name', type: 'string'),
                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                    new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                    new OA\Property(property: 'notifications_count', type: 'integer')
                ]
            )
        )
    )]
    public function list(): JsonResponse
    {
        $users = $this->userService->getAllUsers();
        $responseData = array_map(fn(UserResponse $response) => $response->toArray(), $users);

        return new JsonResponse($responseData, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    #[OA\Get(
        path: '/api/users/{id}',
        summary: 'Get user details',
        description: 'Returns detailed information about a specific user.',
        tags: ['Users']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'User ID',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'User details',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'email', type: 'string'),
                new OA\Property(property: 'first_name', type: 'string'),
                new OA\Property(property: 'last_name', type: 'string'),
                new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                new OA\Property(property: 'notifications_count', type: 'integer')
            ]
        )
    )]
    #[OA\Response(response: 404, description: 'User not found')]
    public function show(int $id): JsonResponse
    {
        $userResponse = $this->userService->getUser($id);

        return new JsonResponse($userResponse->toArray(), Response::HTTP_OK);
    }
}
