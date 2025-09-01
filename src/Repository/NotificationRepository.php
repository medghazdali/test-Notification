<?php

namespace App\Repository;

use App\Entity\Notification;
use App\Enum\NotificationStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Notification>
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    public function save(Notification $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Notification $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByStatus(NotificationStatus $status): array
    {
        return $this->findBy(['status' => $status]);
    }

    public function findPendingNotifications(): array
    {
        return $this->findByStatus(NotificationStatus::PENDING);
    }

    public function findSentNotifications(): array
    {
        return $this->findByStatus(NotificationStatus::SENT);
    }

    public function findFailedNotifications(): array
    {
        return $this->findByStatus(NotificationStatus::FAILED);
    }

    public function findByUser(int $userId): array
    {
        return $this->findBy(['user' => $userId]);
    }
}
