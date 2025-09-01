<?php

namespace App\Repository;

use App\Entity\EmailTemplate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EmailTemplate>
 */
class EmailTemplateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmailTemplate::class);
    }

    public function save(EmailTemplate $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(EmailTemplate $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByName(string $name): ?EmailTemplate
    {
        return $this->findOneBy(['name' => $name]);
    }

    public function findActiveTemplates(): array
    {
        return $this->createQueryBuilder('et')
            ->orderBy('et.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
