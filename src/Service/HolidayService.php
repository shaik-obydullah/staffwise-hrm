<?php

namespace App\Service;

use App\Entity\Employee;
use App\Entity\HolidayBooking;
use App\Entity\HolidayLedgerEntry;
use App\Entity\PayrollRun;
use App\Entity\Payslip;
use App\Repository\HolidayLedgerEntryRepository;
use Doctrine\ORM\EntityManagerInterface;

class HolidayService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly HolidayLedgerEntryRepository $ledgerRepository,
    ) {
    }

    public function getBalance(Employee $employee): float
    {
        return $this->ledgerRepository->getBalanceForEmployee($employee->getId());
    }

    public function getAccrued(Employee $employee): float
    {
        return $this->ledgerRepository->getAccruedForEmployee($employee->getId());
    }

    public function getTaken(Employee $employee): float
    {
        return $this->ledgerRepository->getTakenForEmployee($employee->getId());
    }

    public function getRemaining(Employee $employee): float
    {
        return round($this->getBalance($employee), 2);
    }

    /**
     * Accrues statutory holiday for every payslip in a finalised payroll run.
     *
     * @return int number of ledger entries created
     */
    public function accrueForPayrollRun(PayrollRun $run): int
    {
        $rate = $run->getTaxYear()?->getHolidayAccrualRate() ?? 12.07;
        $created = 0;

        foreach ($run->getPayslips() as $payslip) {
            if ($payslip->getHoursWorked() <= 0) {
                continue;
            }

            $existing = $this->entityManager->getRepository(HolidayLedgerEntry::class)->findOneBy([
                'payrollRun' => $run,
                'employee' => $payslip->getEmployee(),
                'type' => HolidayLedgerEntry::TYPE_ACCRUAL,
            ]);

            if (null !== $existing) {
                continue;
            }

            $hours = round($payslip->getHoursWorked() * $rate / 100, 2);

            if ($hours <= 0) {
                continue;
            }

            $entry = (new HolidayLedgerEntry())
                ->setEmployee($payslip->getEmployee())
                ->setType(HolidayLedgerEntry::TYPE_ACCRUAL)
                ->setHours($hours)
                ->setDescription(sprintf('Holiday accrued at %s%% for payroll period %s', $rate, $run->getPeriodStart()?->format('j M Y')))
                ->setEffectiveDate($run->getPayDate() ?? new \DateTimeImmutable('today'))
                ->setPayrollRun($run);

            $this->entityManager->persist($entry);
            ++$created;
        }

        return $created;
    }

    public function book(Employee $employee, \DateTimeImmutable $start, \DateTimeImmutable $end, float $hours, ?string $note): HolidayBooking
    {
        $booking = (new HolidayBooking())
            ->setEmployee($employee)
            ->setStartDate($start)
            ->setEndDate($end)
            ->setHours($hours)
            ->setNote($note)
            ->setStatus(HolidayBooking::STATUS_BOOKED);

        $entry = (new HolidayLedgerEntry())
            ->setEmployee($employee)
            ->setType(HolidayLedgerEntry::TYPE_BOOKING)
            ->setHours(-1 * abs($hours))
            ->setDescription(sprintf('Holiday booked %s – %s', $start->format('j M Y'), $end->format('j M Y')))
            ->setEffectiveDate($start)
            ->setHolidayBooking($booking);

        $this->entityManager->persist($booking);
        $this->entityManager->persist($entry);

        return $booking;
    }

    public function cancel(HolidayBooking $booking): void
    {
        if (HolidayBooking::STATUS_CANCELLED === $booking->getStatus()) {
            return;
        }

        $booking->setStatus(HolidayBooking::STATUS_CANCELLED);
        $booking->setCancelledAt(new \DateTimeImmutable());

        $entry = $this->entityManager->getRepository(HolidayLedgerEntry::class)->findOneBy(['holidayBooking' => $booking]);

        if (null !== $entry) {
            $restoration = (new HolidayLedgerEntry())
                ->setEmployee($booking->getEmployee())
                ->setType(HolidayLedgerEntry::TYPE_CANCELLATION)
                ->setHours(abs($entry->getHours()))
                ->setDescription(sprintf('Holiday cancelled %s – %s', $booking->getStartDate()?->format('j M Y'), $booking->getEndDate()?->format('j M Y')))
                ->setEffectiveDate(new \DateTimeImmutable('today'))
                ->setHolidayBooking($booking);

            $this->entityManager->persist($restoration);
        }
    }

    public function markTaken(HolidayBooking $booking): void
    {
        if (HolidayBooking::STATUS_BOOKED !== $booking->getStatus()) {
            return;
        }

        $booking->setStatus(HolidayBooking::STATUS_TAKEN);

        $entry = $this->entityManager->getRepository(HolidayLedgerEntry::class)->findOneBy(['holidayBooking' => $booking]);

        if (null !== $entry && HolidayLedgerEntry::TYPE_BOOKING === $entry->getType()) {
            $entry->setType(HolidayLedgerEntry::TYPE_TAKEN);
        }
    }

    public function adjust(Employee $employee, float $hours, string $description, ?\DateTimeImmutable $date = null): HolidayLedgerEntry
    {
        $entry = (new HolidayLedgerEntry())
            ->setEmployee($employee)
            ->setType(HolidayLedgerEntry::TYPE_ADJUSTMENT)
            ->setHours($hours)
            ->setDescription($description)
            ->setEffectiveDate($date ?? new \DateTimeImmutable('today'));

        $this->entityManager->persist($entry);

        return $entry;
    }

    public function recordPayslipHolidayPay(Payslip $payslip): void
    {
        if ($payslip->getHoursWorked() <= 0) {
            return;
        }
    }

    /**
     * Estimate working hours between two dates (Monday to Friday) using the
     * employee's contracted daily hours.
     */
    public function estimateWorkingHours(Employee $employee, \DateTimeImmutable $start, \DateTimeImmutable $end): float
    {
        if ($end < $start) {
            return 0.0;
        }

        $daily = $employee->getContractedWeeklyHours() / 5;
        $days = 0;
        $cursor = $start;

        while ($cursor <= $end) {
            if ((int) $cursor->format('N') <= 5) {
                ++$days;
            }
            $cursor = $cursor->modify('+1 day');
        }

        return round($days * $daily, 2);
    }
}
