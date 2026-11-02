<?php

namespace App\Repository;

use App\Entity\Payslip;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Payslip>
 */
class PayslipRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Payslip::class);
    }

    /** @return list<Payslip> */
    public function findByEmployeeOrdered(int $employeeId): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.employee = :employee')
            ->setParameter('employee', $employeeId)
            ->orderBy('p.createdAt', \SortDirection::Descending)
            ->getQuery()
            ->getResult();
    }

    public function findOneForRunAndEmployee(int $runId, int $employeeId): ?Payslip
    {
        return $this->findOneBy(['payrollRun' => $runId, 'employee' => $employeeId]);
    }
}
