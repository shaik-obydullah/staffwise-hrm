<?php

namespace App\Repository;

use App\Entity\HolidayLedgerEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HolidayLedgerEntry>
 */
class HolidayLedgerEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HolidayLedgerEntry::class);
    }

    public function getBalanceForEmployee(int $employeeId): float
    {
        $result = $this->createQueryBuilder('h')
            ->select('COALESCE(SUM(h.hours), 0)')
            ->andWhere('h.employee = :employee')
            ->setParameter('employee', $employeeId)
            ->getQuery()
            ->getSingleScalarResult();

        return round((float) $result, 2);
    }

    /** @return list<HolidayLedgerEntry> */
    public function findByEmployeeOrdered(int $employeeId): array
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.employee = :employee')
            ->setParameter('employee', $employeeId)
            ->orderBy('h.effectiveDate', \SortDirection::Descending)
            ->addOrderBy('h.id', \SortDirection::Descending)
            ->getQuery()
            ->getResult();
    }

    public function getAccruedForEmployee(int $employeeId): float
    {
        $result = $this->createQueryBuilder('h')
            ->select('COALESCE(SUM(h.hours), 0)')
            ->andWhere('h.employee = :employee')
            ->andWhere('h.type = :type')
            ->setParameter('employee', $employeeId)
            ->setParameter('type', HolidayLedgerEntry::TYPE_ACCRUAL)
            ->getQuery()
            ->getSingleScalarResult();

        return round((float) $result, 2);
    }

    public function getTakenForEmployee(int $employeeId): float
    {
        $result = $this->createQueryBuilder('h')
            ->select('COALESCE(SUM(h.hours), 0)')
            ->andWhere('h.employee = :employee')
            ->andWhere('h.type IN (:types)')
            ->setParameter('employee', $employeeId)
            ->setParameter('types', [HolidayLedgerEntry::TYPE_TAKEN, HolidayLedgerEntry::TYPE_BOOKING])
            ->getQuery()
            ->getSingleScalarResult();

        return round(abs((float) $result), 2);
    }
}
