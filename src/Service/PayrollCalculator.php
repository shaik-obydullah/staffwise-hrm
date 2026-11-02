<?php

namespace App\Service;

use App\Entity\Employee;
use App\Entity\TaxYear;

/**
 * Calculates UK PAYE, National Insurance, pension, student loan and net pay.
 *
 * Payroll is calculated on a period basis using annualised thresholds. This
 * mirrors the non-cumulative "week 1 / month 1" method, which keeps each
 * payroll run self-contained and reproducible.
 */
class PayrollCalculator
{
    public const OVERTIME_MULTIPLIER = 1.5;

    public function calculate(
        Employee $employee,
        float $hoursWorked,
        float $overtimeHours,
        float $bonus,
        TaxYear $taxYear,
        ?string $frequency = null,
    ): PayrollResult {
        $frequency ??= $employee->getPayFrequency();
        $periods = $this->periodsPerYear($frequency);
        $hourlyRate = $employee->getHourlyRate();

        $basicPay = round(max(0, $hoursWorked) * $hourlyRate, 2);
        $overtimePay = round(max(0, $overtimeHours) * $hourlyRate * self::OVERTIME_MULTIPLIER, 2);
        $bonus = round(max(0, $bonus), 2);
        $grossPay = round($basicPay + $overtimePay + $bonus, 2);

        $annualGross = $grossPay * $periods;

        $paye = round($this->incomeTax($annualGross, $employee->getTaxCode(), $taxYear) / $periods, 2);
        $employeeNi = round($this->employeeNi($annualGross, $employee->getNiCategory(), $taxYear) / $periods, 2);
        $employerNi = round($this->employerNi($annualGross, $taxYear) / $periods, 2);
        $studentLoan = round($this->studentLoan($annualGross, $employee->getStudentLoanPlan(), $taxYear) / $periods, 2);

        [$pensionEmployee, $pensionEmployer] = $this->pension($annualGross, $employee, $taxYear);
        $pensionEmployee = round($pensionEmployee / $periods, 2);
        $pensionEmployer = round($pensionEmployer / $periods, 2);

        $netPay = round($grossPay - $paye - $employeeNi - $pensionEmployee - $studentLoan, 2);
        $employerCost = round($grossPay + $employerNi + $pensionEmployer, 2);

        return new PayrollResult(
            hourlyRate: $hourlyRate,
            basicPay: $basicPay,
            overtimePay: $overtimePay,
            bonus: $bonus,
            grossPay: $grossPay,
            paye: max(0, $paye),
            employeeNi: max(0, $employeeNi),
            employerNi: max(0, $employerNi),
            pensionEmployee: max(0, $pensionEmployee),
            pensionEmployer: max(0, $pensionEmployer),
            studentLoan: max(0, $studentLoan),
            netPay: max(0, $netPay),
            employerCost: max(0, $employerCost),
        );
    }

    public function periodsPerYear(string $frequency): int
    {
        return match ($frequency) {
            Employee::FREQUENCY_WEEKLY => 52,
            Employee::FREQUENCY_MONTHLY => 12,
            default => 12,
        };
    }

    private function incomeTax(float $annualGross, string $taxCode, TaxYear $taxYear): float
    {
        $code = strtoupper(trim($taxCode));

        $taxable = max(0, $annualGross);

        if ('NT' === $code) {
            return 0.0;
        }

        if ('BR' === $code) {
            return $taxable * $taxYear->getBasicRate() / 100;
        }

        if ('D0' === $code) {
            return $taxable * $taxYear->getHigherRate() / 100;
        }

        if ('D1' === $code) {
            return $taxable * $taxYear->getAdditionalRate() / 100;
        }

        $allowance = $this->personalAllowance($code, $taxYear);

        if ($allowance > 0 && $annualGross > 100000) {
            $allowance -= ($annualGross - 100000) / 2;
        }

        $allowance = max(0, $allowance);
        $taxable = max(0, $annualGross - $allowance);

        $basicBand = max(0, $taxYear->getBasicRateLimit() - $taxYear->getPersonalAllowance());
        $higherBand = max(0, $taxYear->getHigherRateLimit() - $taxYear->getBasicRateLimit());

        $tax = 0.0;
        $remaining = $taxable;

        $atBasic = min($remaining, $basicBand);
        $tax += $atBasic * $taxYear->getBasicRate() / 100;
        $remaining -= $atBasic;

        $atHigher = min($remaining, $higherBand);
        $tax += $atHigher * $taxYear->getHigherRate() / 100;
        $remaining -= $atHigher;

        $tax += $remaining * $taxYear->getAdditionalRate() / 100;

        return $tax;
    }

    private function personalAllowance(string $code, TaxYear $taxYear): float
    {
        if (preg_match('/^(\d+)\s*[LMNTPY]$/', $code, $matches)) {
            return (float) $matches[1] * 10;
        }

        if (preg_match('/^K(\d+)$/', $code, $matches)) {
            return -1 * (float) $matches[1] * 10;
        }

        if (preg_match('/^0T$/', $code)) {
            return 0.0;
        }

        return $taxYear->getPersonalAllowance();
    }

    private function employeeNi(float $annualGross, string $category, TaxYear $taxYear): float
    {
        // Category C is employer-only; categories B and Z pay a reduced rate in
        // real HMRC tables. We apply category C's zero employee contribution and
        // use the standard rate for all other categories.
        if ('C' === strtoupper($category)) {
            return 0.0;
        }

        $primary = $taxYear->getNiPrimaryThreshold();
        $upper = $taxYear->getNiUpperEarningsLimit();

        if ($annualGross <= $primary) {
            return 0.0;
        }

        $main = min($annualGross, $upper) - $primary;
        $ni = $main * $taxYear->getNiEmployeeRate() / 100;

        if ($annualGross > $upper) {
            $ni += ($annualGross - $upper) * $taxYear->getNiEmployeeUpperRate() / 100;
        }

        return $ni;
    }

    private function employerNi(float $annualGross, TaxYear $taxYear): float
    {
        $threshold = $taxYear->getEmployerNiThreshold();

        if ($annualGross <= $threshold) {
            return 0.0;
        }

        return ($annualGross - $threshold) * $taxYear->getNiEmployerRate() / 100;
    }

    /**
     * @return array{0: float, 1: float} employee and employer annual contributions
     */
    private function pension(float $annualGross, Employee $employee, TaxYear $taxYear): array
    {
        if (!$employee->isPensionOptIn()) {
            return [0.0, 0.0];
        }

        $lower = $taxYear->getPensionLower();
        $upper = $taxYear->getPensionUpper();

        if ($annualGross <= $lower) {
            return [0.0, 0.0];
        }

        $qualifying = min($annualGross, $upper) - $lower;

        $employeeRate = $employee->getPensionEmployeeRate() ?? $taxYear->getPensionEmployeeRate();
        $employerRate = $employee->getPensionEmployerRate() ?? $taxYear->getPensionEmployerRate();

        return [
            $qualifying * $employeeRate / 100,
            $qualifying * $employerRate / 100,
        ];
    }

    private function studentLoan(float $annualGross, string $plan, TaxYear $taxYear): float
    {
        if (Employee::LOAN_NONE === $plan) {
            return 0.0;
        }

        $threshold = $taxYear->getStudentLoanThreshold($plan);

        if (null === $threshold || $annualGross <= $threshold) {
            return 0.0;
        }

        return ($annualGross - $threshold) * $taxYear->getStudentLoanRateForPlan($plan) / 100;
    }
}
