<?php

namespace App\Entity;

use App\Repository\PayrollRunRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PayrollRunRepository::class)]
#[ORM\Table(name: 'payroll_run')]
class PayrollRun
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_FINALISED = 'finalised';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?TaxYear $taxYear = null;

    #[ORM\Column(length: 20)]
    private string $frequency = Employee::FREQUENCY_MONTHLY;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotNull]
    private ?\DateTimeImmutable $periodStart = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotNull]
    private ?\DateTimeImmutable $periodEnd = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotNull]
    private ?\DateTimeImmutable $payDate = null;

    #[ORM\Column(length: 20)]
    private string $status = self::STATUS_DRAFT;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column]
    private float $grossTotal = 0.0;

    #[ORM\Column]
    private float $payeTotal = 0.0;

    #[ORM\Column]
    private float $employeeNiTotal = 0.0;

    #[ORM\Column]
    private float $employerNiTotal = 0.0;

    #[ORM\Column]
    private float $pensionEmployeeTotal = 0.0;

    #[ORM\Column]
    private float $pensionEmployerTotal = 0.0;

    #[ORM\Column]
    private float $studentLoanTotal = 0.0;

    #[ORM\Column]
    private float $netTotal = 0.0;

    #[ORM\Column]
    private float $employerCostTotal = 0.0;

    #[ORM\Column]
    private float $employmentAllowanceApplied = 0.0;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $finalisedAt = null;

    /** @var Collection<int, Payslip> */
    #[ORM\OneToMany(mappedBy: 'payrollRun', targetEntity: Payslip::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['id' => \SortDirection::Ascending])]
    private Collection $payslips;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->payslips = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTaxYear(): ?TaxYear
    {
        return $this->taxYear;
    }

    public function setTaxYear(?TaxYear $taxYear): static
    {
        $this->taxYear = $taxYear;

        return $this;
    }

    public function getFrequency(): string
    {
        return $this->frequency;
    }

    public function setFrequency(string $frequency): static
    {
        $this->frequency = $frequency;

        return $this;
    }

    public function getPeriodStart(): ?\DateTimeImmutable
    {
        return $this->periodStart;
    }

    public function setPeriodStart(?\DateTimeImmutable $periodStart): static
    {
        $this->periodStart = $periodStart;

        return $this;
    }

    public function getPeriodEnd(): ?\DateTimeImmutable
    {
        return $this->periodEnd;
    }

    public function setPeriodEnd(?\DateTimeImmutable $periodEnd): static
    {
        $this->periodEnd = $periodEnd;

        return $this;
    }

    public function getPayDate(): ?\DateTimeImmutable
    {
        return $this->payDate;
    }

    public function setPayDate(?\DateTimeImmutable $payDate): static
    {
        $this->payDate = $payDate;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function isDraft(): bool
    {
        return self::STATUS_DRAFT === $this->status;
    }

    public function isFinalised(): bool
    {
        return self::STATUS_FINALISED === $this->status;
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

    public function getGrossTotal(): float
    {
        return $this->grossTotal;
    }

    public function setGrossTotal(float $v): static
    {
        $this->grossTotal = $v;

        return $this;
    }

    public function getPayeTotal(): float
    {
        return $this->payeTotal;
    }

    public function setPayeTotal(float $v): static
    {
        $this->payeTotal = $v;

        return $this;
    }

    public function getEmployeeNiTotal(): float
    {
        return $this->employeeNiTotal;
    }

    public function setEmployeeNiTotal(float $v): static
    {
        $this->employeeNiTotal = $v;

        return $this;
    }

    public function getEmployerNiTotal(): float
    {
        return $this->employerNiTotal;
    }

    public function setEmployerNiTotal(float $v): static
    {
        $this->employerNiTotal = $v;

        return $this;
    }

    public function getPensionEmployeeTotal(): float
    {
        return $this->pensionEmployeeTotal;
    }

    public function setPensionEmployeeTotal(float $v): static
    {
        $this->pensionEmployeeTotal = $v;

        return $this;
    }

    public function getPensionEmployerTotal(): float
    {
        return $this->pensionEmployerTotal;
    }

    public function setPensionEmployerTotal(float $v): static
    {
        $this->pensionEmployerTotal = $v;

        return $this;
    }

    public function getStudentLoanTotal(): float
    {
        return $this->studentLoanTotal;
    }

    public function setStudentLoanTotal(float $v): static
    {
        $this->studentLoanTotal = $v;

        return $this;
    }

    public function getNetTotal(): float
    {
        return $this->netTotal;
    }

    public function setNetTotal(float $v): static
    {
        $this->netTotal = $v;

        return $this;
    }

    public function getEmployerCostTotal(): float
    {
        return $this->employerCostTotal;
    }

    public function setEmployerCostTotal(float $v): static
    {
        $this->employerCostTotal = $v;

        return $this;
    }

    public function getEmploymentAllowanceApplied(): float
    {
        return $this->employmentAllowanceApplied;
    }

    public function setEmploymentAllowanceApplied(float $v): static
    {
        $this->employmentAllowanceApplied = $v;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getFinalisedAt(): ?\DateTimeImmutable
    {
        return $this->finalisedAt;
    }

    public function setFinalisedAt(?\DateTimeImmutable $finalisedAt): static
    {
        $this->finalisedAt = $finalisedAt;

        return $this;
    }

    /** @return Collection<int, Payslip> */
    public function getPayslips(): Collection
    {
        return $this->payslips;
    }

    public function addPayslip(Payslip $payslip): static
    {
        if (!$this->payslips->contains($payslip)) {
            $this->payslips->add($payslip);
            $payslip->setPayrollRun($this);
        }

        return $this;
    }

    public function removePayslip(Payslip $payslip): static
    {
        if ($this->payslips->removeElement($payslip) && $payslip->getPayrollRun() === $this) {
            $payslip->setPayrollRun(null);
        }

        return $this;
    }

    public function getHmrcLiabilityTotal(): float
    {
        return round(
            $this->payeTotal
            + $this->employeeNiTotal
            + max(0, $this->employerNiTotal - $this->employmentAllowanceApplied)
            + $this->studentLoanTotal,
            2
        );
    }

    public function getTotalDeductions(): float
    {
        return round(
            $this->payeTotal
            + $this->employeeNiTotal
            + $this->pensionEmployeeTotal
            + $this->studentLoanTotal,
            2
        );
    }

    public function getPaymentReference(): string
    {
        return sprintf('SW-%s-%04d', preg_replace('/[^0-9A-Za-z]/', '', (string) $this->taxYear?->getName()), $this->id ?? 0);
    }

    public function getHmrcDueDate(): ?\DateTimeImmutable
    {
        return $this->payDate?->modify('+22 days');
    }

    public function recalculateTotals(): void
    {
        $gross = $paye = $employeeNi = $employerNi = $pensionEmployee = $pensionEmployer = $studentLoan = $net = $employerCost = 0.0;

        foreach ($this->payslips as $payslip) {
            $gross += $payslip->getGrossPay();
            $paye += $payslip->getPaye();
            $employeeNi += $payslip->getEmployeeNi();
            $employerNi += $payslip->getEmployerNi();
            $pensionEmployee += $payslip->getPensionEmployee();
            $pensionEmployer += $payslip->getPensionEmployer();
            $studentLoan += $payslip->getStudentLoan();
            $net += $payslip->getNetPay();
            $employerCost += $payslip->getEmployerCost();
        }

        $allowance = min($this->taxYear?->getEmploymentAllowance() ?? 0, $employerNi);

        $this->grossTotal = round($gross, 2);
        $this->payeTotal = round($paye, 2);
        $this->employeeNiTotal = round($employeeNi, 2);
        $this->employerNiTotal = round($employerNi, 2);
        $this->pensionEmployeeTotal = round($pensionEmployee, 2);
        $this->pensionEmployerTotal = round($pensionEmployer, 2);
        $this->studentLoanTotal = round($studentLoan, 2);
        $this->netTotal = round($net, 2);
        $this->employerCostTotal = round($employerCost, 2);
        $this->employmentAllowanceApplied = round($allowance, 2);
    }
}
