<?php

namespace App\Controller;

use App\DTO\CreateEmailTemplateRequest;
use App\DTO\EmailTemplateResponse;
use App\Service\EmailTemplateService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/email-templates', name: 'api_email_templates_')]
#[OA\Tag(name: 'Email Templates', description: 'Email template management endpoints')]
class EmailTemplateController extends AbstractController
{
    public function __construct(
        private EmailTemplateService $emailTemplateService
    ) {
    }

    #[Route('', name: 'create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/email-templates',
        summary: 'Create a new email template',
        description: 'Creates a new email template with subject and body templates for both HTML and plain text.',
        tags: ['Email Templates']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['name', 'subject_template', 'html_body_template', 'plain_text_body_template'],
            properties: [
                new OA\Property(property: 'name', type: 'string', maxLength: 255, description: 'Template name'),
                new OA\Property(property: 'subject_template', type: 'string', maxLength: 255, description: 'Email subject template with placeholders'),
                new OA\Property(property: 'html_body_template', type: 'string', description: 'HTML email body template'),
                new OA\Property(property: 'plain_text_body_template', type: 'string', description: 'Plain text email body template')
            ],
            example: [
                'name' => 'Welcome Email',
                'subject_template' => 'Welcome, {first_name}!',
                'html_body_template' => '<h1>Welcome, {first_name}!</h1><p>Thank you for joining us.</p>',
                'plain_text_body_template' => 'Welcome, {first_name}!\n\nThank you for joining us.'
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Email template created successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'subject_template', type: 'string'),
                new OA\Property(property: 'html_body_template', type: 'string'),
                new OA\Property(property: 'plain_text_body_template', type: 'string'),
                new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                new OA\Property(property: 'notifications_count', type: 'integer')
            ]
        )
    )]
    #[OA\Response(response: 400, description: 'Bad request - validation failed')]
    #[OA\Response(response: 409, description: 'Conflict - template name already exists')]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!$data) {
            return new JsonResponse(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        $createRequest = new CreateEmailTemplateRequest($data);
        $emailTemplateResponse = $this->emailTemplateService->createEmailTemplate($createRequest);

        return new JsonResponse($emailTemplateResponse->toArray(), Response::HTTP_CREATED);
    }

    #[Route('', name: 'list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/email-templates',
        summary: 'List all email templates',
        description: 'Returns all email templates in the system with their usage counts.',
        tags: ['Email Templates']
    )]
    #[OA\Response(
        response: 200,
        description: 'List of all email templates',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'id', type: 'integer'),
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'subject_template', type: 'string'),
                    new OA\Property(property: 'html_body_template', type: 'string'),
                    new OA\Property(property: 'plain_text_body_template', type: 'string'),
                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                    new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                    new OA\Property(property: 'notifications_count', type: 'integer')
                ]
            )
        )
    )]
    public function list(): JsonResponse
    {
        $emailTemplates = $this->emailTemplateService->getAllEmailTemplates();
        $responseData = array_map(fn(EmailTemplateResponse $response) => $response->toArray(), $emailTemplates);

        return new JsonResponse($responseData, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    #[OA\Get(
        path: '/api/email-templates/{id}',
        summary: 'Get email template details',
        description: 'Returns detailed information about a specific email template.',
        tags: ['Email Templates']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'Email template ID',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Email template details',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'subject_template', type: 'string'),
                new OA\Property(property: 'html_body_template', type: 'string'),
                new OA\Property(property: 'plain_text_body_template', type: 'string'),
                new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                new OA\Property(property: 'notifications_count', type: 'integer')
            ]
        )
    )]
    #[OA\Response(response: 404, description: 'Email template not found')]
    public function show(int $id): JsonResponse
    {
        $emailTemplateResponse = $this->emailTemplateService->getEmailTemplate($id);

        return new JsonResponse($emailTemplateResponse->toArray(), Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/email-templates/{id}',
        summary: 'Update an email template',
        description: 'Updates an existing email template with new content.',
        tags: ['Email Templates']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'Email template ID',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['name', 'subject_template', 'html_body_template', 'plain_text_body_template'],
            properties: [
                new OA\Property(property: 'name', type: 'string', maxLength: 255, description: 'Template name'),
                new OA\Property(property: 'subject_template', type: 'string', maxLength: 255, description: 'Email subject template with placeholders'),
                new OA\Property(property: 'html_body_template', type: 'string', description: 'HTML email body template'),
                new OA\Property(property: 'plain_text_body_template', type: 'string', description: 'Plain text email body template')
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Email template updated successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'subject_template', type: 'string'),
                new OA\Property(property: 'html_body_template', type: 'string'),
                new OA\Property(property: 'plain_text_body_template', type: 'string'),
                new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                new OA\Property(property: 'notifications_count', type: 'integer')
            ]
        )
    )]
    #[OA\Response(response: 400, description: 'Bad request - validation failed')]
    #[OA\Response(response: 404, description: 'Email template not found')]
    #[OA\Response(response: 409, description: 'Conflict - template name already exists')]
    public function update(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!$data) {
            return new JsonResponse(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        $updateRequest = new CreateEmailTemplateRequest($data);
        $emailTemplateResponse = $this->emailTemplateService->updateEmailTemplate($id, $updateRequest);

        return new JsonResponse($emailTemplateResponse->toArray(), Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/email-templates/{id}',
        summary: 'Delete an email template',
        description: 'Deletes an email template. Cannot delete templates that are being used by notifications.',
        tags: ['Email Templates']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'Email template ID',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 204,
        description: 'Email template deleted successfully'
    )]
    #[OA\Response(response: 404, description: 'Email template not found')]
    #[OA\Response(response: 400, description: 'Bad request - template is in use')]
    public function delete(int $id): JsonResponse
    {
        $this->emailTemplateService->deleteEmailTemplate($id);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
