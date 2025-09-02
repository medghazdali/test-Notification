<?php

namespace App\Service;

use App\DTO\CreateEmailTemplateRequest;
use App\DTO\EmailTemplateResponse;
use App\Entity\EmailTemplate;
use App\Exception\EmailTemplateException;
use App\Repository\EmailTemplateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EmailTemplateService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        private EmailTemplateRepository $emailTemplateRepository
    ) {
    }

    public function createEmailTemplate(CreateEmailTemplateRequest $request): EmailTemplateResponse
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

        // Check if template with name already exists
        $existingTemplate = $this->emailTemplateRepository->findByName($request->name);
        if ($existingTemplate) {
            throw EmailTemplateException::nameAlreadyExists($request->name);
        }

        // Create email template entity
        $emailTemplate = new EmailTemplate();
        $emailTemplate->setName($request->name);
        $emailTemplate->setSubjectTemplate($request->subjectTemplate);
        $emailTemplate->setHtmlBodyTemplate($request->htmlBodyTemplate);
        $emailTemplate->setPlainTextBodyTemplate($request->plainTextBodyTemplate);

        // Validate the entity
        $errors = $this->validator->validate($emailTemplate);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            throw new \InvalidArgumentException('Entity validation failed: ' . implode(', ', $errorMessages));
        }

        // Persist and flush
        $this->entityManager->persist($emailTemplate);
        $this->entityManager->flush();

        return EmailTemplateResponse::fromEntity($emailTemplate);
    }

    public function getEmailTemplate(int $id): EmailTemplateResponse
    {
        $emailTemplate = $this->emailTemplateRepository->find($id);
        if (!$emailTemplate) {
            throw EmailTemplateException::notFound($id);
        }

        return EmailTemplateResponse::fromEntity($emailTemplate);
    }

    public function getAllEmailTemplates(): array
    {
        $emailTemplates = $this->emailTemplateRepository->findAll();
        
        return array_map(
            fn(EmailTemplate $emailTemplate) => EmailTemplateResponse::fromEntity($emailTemplate),
            $emailTemplates
        );
    }

    public function updateEmailTemplate(int $id, CreateEmailTemplateRequest $request): EmailTemplateResponse
    {
        $emailTemplate = $this->emailTemplateRepository->find($id);
        if (!$emailTemplate) {
            throw EmailTemplateException::notFound($id);
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

        // Check if another template with the same name exists (excluding current one)
        $existingTemplate = $this->emailTemplateRepository->findByName($request->name);
        if ($existingTemplate && $existingTemplate->getId() !== $id) {
            throw EmailTemplateException::nameAlreadyExists($request->name);
        }

        // Update the template
        $emailTemplate->setName($request->name);
        $emailTemplate->setSubjectTemplate($request->subjectTemplate);
        $emailTemplate->setHtmlBodyTemplate($request->htmlBodyTemplate);
        $emailTemplate->setPlainTextBodyTemplate($request->plainTextBodyTemplate);

        // Validate the entity
        $errors = $this->validator->validate($emailTemplate);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            throw new \InvalidArgumentException('Entity validation failed: ' . implode(', ', $errorMessages));
        }

        $this->entityManager->flush();

        return EmailTemplateResponse::fromEntity($emailTemplate);
    }

    public function deleteEmailTemplate(int $id): void
    {
        $emailTemplate = $this->emailTemplateRepository->find($id);
        if (!$emailTemplate) {
            throw EmailTemplateException::notFound($id);
        }

        // Check if template is being used by any notifications
        if ($emailTemplate->getNotifications()->count() > 0) {
            throw EmailTemplateException::cannotDeleteInUse($id);
        }

        $this->entityManager->remove($emailTemplate);
        $this->entityManager->flush();
    }
}
