<?php

namespace App\Service;

use App\Entity\AuditLog;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class AuditLogger
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security,
    ) {
    }

    public function log(string $action, ?string $entityType = null, ?int $entityId = null, ?string $details = null): void
    {
        $actor = $this->security->getUser()?->getUserIdentifier() ?? 'system';

        $entry = (new AuditLog())
            ->setAction($action)
            ->setEntityType($entityType)
            ->setEntityId($entityId)
            ->setDetails($details)
            ->setActor($actor);

        $this->entityManager->persist($entry);
        $this->entityManager->flush();
    }
}
