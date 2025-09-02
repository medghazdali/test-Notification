<?php

namespace App\EventListener;

use App\Exception\EmailTemplateException;
use App\Exception\NotificationAttachmentException;
use App\Exception\NotificationException;
use App\Exception\UserException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

class ApiExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();

        // Only handle API requests
        if (!str_starts_with($request->getPathInfo(), '/api/')) {
            return;
        }

        $response = $this->createErrorResponse($exception);
        $event->setResponse($response);
    }

    private function createErrorResponse(\Throwable $exception): JsonResponse
    {
        $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        $message = 'Internal server error';
        $details = null;

        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
            $message = $exception->getMessage();
        } elseif ($exception instanceof NotificationException) {
            $statusCode = $this->getStatusCodeForNotificationException($exception);
            $message = $exception->getMessage();
        } elseif ($exception instanceof UserException) {
            $statusCode = $this->getStatusCodeForUserException($exception);
            $message = $exception->getMessage();
        } elseif ($exception instanceof EmailTemplateException) {
            $statusCode = $this->getStatusCodeForEmailTemplateException($exception);
            $message = $exception->getMessage();
        } elseif ($exception instanceof NotificationAttachmentException) {
            $statusCode = $this->getStatusCodeForNotificationAttachmentException($exception);
            $message = $exception->getMessage();
        } elseif ($exception instanceof ValidationFailedException) {
            $statusCode = Response::HTTP_BAD_REQUEST;
            $message = 'Validation failed';
            $details = [];
            foreach ($exception->getViolations() as $violation) {
                $details[] = $violation->getMessage();
            }
        } elseif ($exception instanceof \InvalidArgumentException) {
            $statusCode = Response::HTTP_BAD_REQUEST;
            $message = $exception->getMessage();
        }

        $responseData = [
            'error' => $message,
            'code' => $statusCode,
        ];

        if ($details !== null) {
            $responseData['details'] = $details;
        }

        return new JsonResponse($responseData, $statusCode);
    }

    private function getStatusCodeForNotificationException(NotificationException $exception): int
    {
        return match (true) {
            str_contains($exception->getMessage(), 'not found') => Response::HTTP_NOT_FOUND,
            str_contains($exception->getMessage(), 'cannot be sent') => Response::HTTP_BAD_REQUEST,
            str_contains($exception->getMessage(), 'must be provided') => Response::HTTP_BAD_REQUEST,
            default => Response::HTTP_BAD_REQUEST,
        };
    }

    private function getStatusCodeForUserException(UserException $exception): int
    {
        return match (true) {
            str_contains($exception->getMessage(), 'not found') => Response::HTTP_NOT_FOUND,
            str_contains($exception->getMessage(), 'already exists') => Response::HTTP_CONFLICT,
            default => Response::HTTP_BAD_REQUEST,
        };
    }

    private function getStatusCodeForEmailTemplateException(EmailTemplateException $exception): int
    {
        return match (true) {
            str_contains($exception->getMessage(), 'not found') => Response::HTTP_NOT_FOUND,
            str_contains($exception->getMessage(), 'already exists') => Response::HTTP_CONFLICT,
            str_contains($exception->getMessage(), 'cannot delete') => Response::HTTP_BAD_REQUEST,
            default => Response::HTTP_BAD_REQUEST,
        };
    }

    private function getStatusCodeForNotificationAttachmentException(NotificationAttachmentException $exception): int
    {
        return match (true) {
            str_contains($exception->getMessage(), 'not found') => Response::HTTP_NOT_FOUND,
            default => Response::HTTP_BAD_REQUEST,
        };
    }
}
