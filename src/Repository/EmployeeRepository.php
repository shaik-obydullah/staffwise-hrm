<?php

namespace App\Repository;

use App\Entity\Employee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Employee>
 */
class EmployeeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Employee::class);
    }

    /** @return list<Employee> */
    public function findActiveOrdered(): array
    {
        return $this->findBy(['status' => Employee::STATUS_ACTIVE], ['lastName' => \SortDirection::Ascending, 'firstName' => \SortDirection::Ascending]);
    }

    /** @return list<Employee> */
    public function findAllOrdered(): array
    {
        return $this->findBy([], ['lastName' => \SortDirection::Ascending, 'firstName' => \SortDirection::Ascending]);
    }

    public function countActive(): int
    {
        return $this->count(['status' => Employee::STATUS_ACTIVE]);
    }

    /** @return list<Employee> */
    public function search(string $term): array
    {
        $qb = $this->createQueryBuilder('e');

        if ('' !== trim($term)) {
            $qb->andWhere('LOWER(e.firstName) LIKE :term OR LOWER(e.lastName) LIKE :term OR LOWER(e.jobTitle) LIKE :term OR LOWER(e.department) LIKE :term')
                ->setParameter('term', '%'.mb_strtolower(trim($term)).'%');
        }

        return $qb->orderBy('e.lastName', \SortDirection::Ascending)->addOrderBy('e.firstName', \SortDirection::Ascending)->getQuery()->getResult();
    }
}
