<?php

namespace App\Entity;

use App\Repository\PayslipRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PayslipRepository::class)]
#[ORM\Table(name: 'payslip')]
#[ORM\UniqueConstraint(name: 'uniq_payslip_run_employee', columns: ['payroll_run_id', 'employee_id'])]
class Payslip
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'payslips')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?PayrollRun $payrollRun = null;

    #[ORM\ManyToOne(inversedBy: 'payslips')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?Employee $employee = null;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    private float $hoursWorked = 0.0;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    private float $overtimeHours = 0.0;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    private float $bonus = 0.0;

    #[ORM\Column(length: 20)]
    private string $taxCode = '';

    #[ORM\Column(length: 2)]
    private string $niCategory = 'A';

    #[ORM\Column]
    private float $hourlyRate = 0.0;

    #[ORM\Column]
    private float $basicPay = 0.0;

    #[ORM\Column]
    private float $overtimePay = 0.0;

    #[ORM\Column]
    private float $grossPay = 0.0;

    #[ORM\Column]
    private float $paye = 0.0;

    #[ORM\Column]
    private float $employeeNi = 0.0;

    #[ORM\Column]
    private float $employerNi = 0.0;

    #[ORM\Column]
    private float $pensionEmployee = 0.0;

    #[ORM\Column]
    private float $pensionEmployer = 0.0;

    #[ORM\Column]
    private float $studentLoan = 0.0;

    #[ORM\Column]
    private float $netPay = 0.0;

    #[ORM\Column]
    private float $employerCost = 0.0;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getEmployee(): ?Employee
    {
        return $this->employee;
    }

    public function setEmployee(?Employee $employee): static
    {
        $this->employee = $employee;

        return $this;
    }

    public function getHoursWorked(): float
    {
        return $this->hoursWorked;
    }

    public function setHoursWorked(float $hoursWorked): static
    {
        $this->hoursWorked = $hoursWorked;

        return $this;
    }

    public function getOvertimeHours(): float
    {
        return $this->overtimeHours;
    }

    public function setOvertimeHours(float $overtimeHours): static
    {
        $this->overtimeHours = $overtimeHours;

        return $this;
    }

    public function getBonus(): float
    {
        return $this->bonus;
    }

    public function setBonus(float $bonus): static
    {
        $this->bonus = $bonus;

        return $this;
    }

    public function getTaxCode(): string
    {
        return $this->taxCode;
    }

    public function setTaxCode(string $taxCode): static
    {
        $this->taxCode = $taxCode;

        return $this;
    }

    public function getNiCategory(): string
    {
        return $this->niCategory;
    }

    public function setNiCategory(string $niCategory): static
    {
        $this->niCategory = $niCategory;

        return $this;
    }

    public function getHourlyRate(): float
    {
        return $this->hourlyRate;
    }

    public function setHourlyRate(float $hourlyRate): static
    {
        $this->hourlyRate = $hourlyRate;

        return $this;
    }

    public function getBasicPay(): float
    {
        return $this->basicPay;
    }

    public function setBasicPay(float $basicPay): static
    {
        $this->basicPay = $basicPay;

        return $this;
    }

    public function getOvertimePay(): float
    {
        return $this->overtimePay;
    }

    public function setOvertimePay(float $overtimePay): static
    {
        $this->overtimePay = $overtimePay;

        return $this;
    }

    public function getGrossPay(): float
    {
        return $this->grossPay;
    }

    public function setGrossPay(float $grossPay): static
    {
        $this->grossPay = $grossPay;

        return $this;
    }

    public function getPaye(): float
    {
        return $this->paye;
    }

    public function setPaye(float $paye): static
    {
        $this->paye = $paye;

        return $this;
    }

    public function getEmployeeNi(): float
    {
        return $this->employeeNi;
    }

    public function setEmployeeNi(float $employeeNi): static
    {
        $this->employeeNi = $employeeNi;

        return $this;
    }

    public function getEmployerNi(): float
    {
        return $this->employerNi;
    }

    public function setEmployerNi(float $employerNi): static
    {
        $this->employerNi = $employerNi;

        return $this;
    }

    public function getPensionEmployee(): float
    {
        return $this->pensionEmployee;
    }

    public function setPensionEmployee(float $pensionEmployee): static
    {
        $this->pensionEmployee = $pensionEmployee;

        return $this;
    }

    public function getPensionEmployer(): float
    {
        return $this->pensionEmployer;
    }

    public function setPensionEmployer(float $pensionEmployer): static
    {
        $this->pensionEmployer = $pensionEmployer;

        return $this;
    }

    public function getStudentLoan(): float
    {
        return $this->studentLoan;
    }

    public function setStudentLoan(float $studentLoan): static
    {
        $this->studentLoan = $studentLoan;

        return $this;
    }

    public function getNetPay(): float
    {
        return $this->netPay;
    }

    public function setNetPay(float $netPay): static
    {
        $this->netPay = $netPay;

        return $this;
    }

    public function getEmployerCost(): float
    {
        return $this->employerCost;
    }

    public function setEmployerCost(float $employerCost): static
    {
        $this->employerCost = $employerCost;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function touch(): static
    {
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getPeriodLabel(): string
    {
        if (null === $this->payrollRun?->getPeriodStart() || null === $this->payrollRun?->getPeriodEnd()) {
            return '';
        }

        return sprintf(
            '%s – %s',
            $this->payrollRun->getPeriodStart()->format('j M Y'),
            $this->payrollRun->getPeriodEnd()->format('j M Y')
        );
    }
}
