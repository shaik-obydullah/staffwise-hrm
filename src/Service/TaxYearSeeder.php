<?php

namespace App\Service;

use App\Entity\TaxYear;
use App\Repository\TaxYearRepository;
use Doctrine\ORM\EntityManagerInterface;

class TaxYearSeeder
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TaxYearRepository $taxYearRepository,
    ) {
    }

    /**
     * Creates the standard UK 2026/27 rate set when no rates exist yet.
     */
    public function seed(): bool
    {
        if (null !== $this->taxYearRepository->findActive() || $this->taxYearRepository->count([]) > 0) {
            return false;
        }

        $taxYear = (new TaxYear())
            ->setName('2026/27')
            ->setEffectiveFrom(new \DateTimeImmutable('2026-04-06'))
            ->setEffectiveTo(new \DateTimeImmutable('2027-04-05'))
            ->setActive(true)
            ->setActivatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($taxYear);
        $this->entityManager->flush();

        return true;
    }
}
