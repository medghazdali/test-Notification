<?php

namespace App\Service;

use App\DTO\CreateUserRequest;
use App\DTO\UserResponse;
use App\Entity\User;
use App\Exception\UserException;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        private UserRepository $userRepository
    ) {
    }

    public function createUser(CreateUserRequest $request): UserResponse
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

        // Check if user with email already exists
        $existingUser = $this->userRepository->findByEmail($request->email);
        if ($existingUser) {
            throw UserException::emailAlreadyExists($request->email);
        }

        // Create user entity
        $user = new User();
        $user->setEmail($request->email);
        $user->setFirstName($request->firstName);
        $user->setLastName($request->lastName);

        // Validate the entity
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            throw new \InvalidArgumentException('Entity validation failed: ' . implode(', ', $errorMessages));
        }

        // Persist and flush
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return UserResponse::fromEntity($user);
    }

    public function getUser(int $id): UserResponse
    {
        $user = $this->userRepository->find($id);
        if (!$user) {
            throw UserException::notFound($id);
        }

        return UserResponse::fromEntity($user);
    }

    public function getAllUsers(): array
    {
        $users = $this->userRepository->findAll();
        
        return array_map(
            fn(User $user) => UserResponse::fromEntity($user),
            $users
        );
    }
}
