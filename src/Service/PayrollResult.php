<?php

namespace App\Service;

/**
 * Immutable result of a single employee payroll calculation.
 */
final readonly class PayrollResult
{
    public function __construct(
        public float $hourlyRate,
        public float $basicPay,
        public float $overtimePay,
        public float $bonus,
        public float $grossPay,
        public float $paye,
        public float $employeeNi,
        public float $employerNi,
        public float $pensionEmployee,
        public float $pensionEmployer,
        public float $studentLoan,
        public float $netPay,
        public float $employerCost,
    ) {
    }
}
