<?php

namespace App\Repository;

use App\Entity\TaxYear;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TaxYear>
 */
class TaxYearRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TaxYear::class);
    }

    public function findActive(): ?TaxYear
    {
        return $this->findOneBy(['active' => true], ['effectiveFrom' => \SortDirection::Descending]);
    }

    /** @return list<TaxYear> */
    public function findAllOrdered(): array
    {
        return $this->findBy([], ['effectiveFrom' => \SortDirection::Descending]);
    }
}
