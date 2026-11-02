<?php

namespace App\Repository;

use App\Entity\PayrollRun;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PayrollRun>
 */
class PayrollRunRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PayrollRun::class);
    }

    /** @return list<PayrollRun> */
    public function findRecent(int $limit = 10): array
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.payDate', \SortDirection::Descending)
            ->addOrderBy('r.id', \SortDirection::Descending)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findLatestFinalised(): ?PayrollRun
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.status = :status')
            ->setParameter('status', PayrollRun::STATUS_FINALISED)
            ->orderBy('r.payDate', \SortDirection::Descending)
            ->addOrderBy('r.id', \SortDirection::Descending)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /** @return list<PayrollRun> */
    public function findFinalisedOrdered(): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.status = :status')
            ->setParameter('status', PayrollRun::STATUS_FINALISED)
            ->orderBy('r.payDate', \SortDirection::Descending)
            ->getQuery()
            ->getResult();
    }

    /** @return list<PayrollRun> */
    public function findDraftOrdered(): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.status = :status')
            ->setParameter('status', PayrollRun::STATUS_DRAFT)
            ->orderBy('r.payDate', \SortDirection::Ascending)
            ->getQuery()
            ->getResult();
    }

    public function getTotalHmrcLiabilityForTaxYear(int $taxYearId): float
    {
        $runs = $this->createQueryBuilder('r')
            ->andWhere('r.taxYear = :taxYear')
            ->andWhere('r.status = :status')
            ->setParameter('taxYear', $taxYearId)
            ->setParameter('status', PayrollRun::STATUS_FINALISED)
            ->getQuery()
            ->getResult();

        $total = 0.0;
        foreach ($runs as $run) {
            $total += $run->getHmrcLiabilityTotal();
        }

        return round($total, 2);
    }
}
