<?php

namespace App\Repository;

use App\Entity\HolidayBooking;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HolidayBooking>
 */
class HolidayBookingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HolidayBooking::class);
    }

    /** @return list<HolidayBooking> */
    public function findByEmployeeOrdered(int $employeeId): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.employee = :employee')
            ->setParameter('employee', $employeeId)
            ->orderBy('b.startDate', \SortDirection::Descending)
            ->getQuery()
            ->getResult();
    }

    /** @return list<HolidayBooking> */
    public function findUpcoming(): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.status = :status')
            ->andWhere('b.startDate >= :today')
            ->setParameter('status', HolidayBooking::STATUS_BOOKED)
            ->setParameter('today', new \DateTimeImmutable('today'))
            ->orderBy('b.startDate', \SortDirection::Ascending)
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }
}
