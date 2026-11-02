<?php

namespace App\Entity;

use App\Repository\HolidayLedgerEntryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HolidayLedgerEntryRepository::class)]
#[ORM\Table(name: 'holiday_ledger_entry')]
class HolidayLedgerEntry
{
    public const TYPE_ACCRUAL = 'accrual';
    public const TYPE_BOOKING = 'booking';
    public const TYPE_TAKEN = 'taken';
    public const TYPE_CANCELLATION = 'cancellation';
    public const TYPE_ADJUSTMENT = 'adjustment';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'holidayLedgerEntries')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Employee $employee = null;

    #[ORM\Column(length: 20)]
    private string $type = self::TYPE_ADJUSTMENT;

    /** Signed hours: positive credits the balance, negative debits it. */
    #[ORM\Column]
    private float $hours = 0.0;

    #[ORM\Column(length: 255)]
    private string $description = '';

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private \DateTimeImmutable $effectiveDate;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?PayrollRun $payrollRun = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?HolidayBooking $holidayBooking = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->effectiveDate = new \DateTimeImmutable('today');
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmployee(): ?Employee
    {
        return $this->employee;
    }

    public function setEmployee(?Employee $employee): static
    {
        $this->employee = $employee;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getHours(): float
    {
        return $this->hours;
    }

    public function setHours(float $hours): static
    {
        $this->hours = $hours;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getEffectiveDate(): \DateTimeImmutable
    {
        return $this->effectiveDate;
    }

    public function setEffectiveDate(\DateTimeImmutable $effectiveDate): static
    {
        $this->effectiveDate = $effectiveDate;

        return $this;
    }

    public function getPayrollRun(): ?PayrollRun
    {
        return $this->payrollRun;
    }

    public function setPayrollRun(?PayrollRun $payrollRun): static
    {
        $this->payrollRun = $payrollRun;

        return $this;
    }

    public function getHolidayBooking(): ?HolidayBooking
    {
        return $this->holidayBooking;
    }

    public function setHolidayBooking(?HolidayBooking $holidayBooking): static
    {
        $this->holidayBooking = $holidayBooking;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
