<?php

namespace App\Entity;

use App\Repository\TaxYearRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TaxYearRepository::class)]
#[ORM\Table(name: 'tax_year')]
class TaxYear
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank]
    private string $name = '';

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotNull]
    private ?\DateTimeImmutable $effectiveFrom = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotNull]
    private ?\DateTimeImmutable $effectiveTo = null;

    #[ORM\Column]
    private bool $active = false;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    private float $personalAllowance = 12570;

    #[ORM\Column]
    #[Assert\Range(min: 0, max: 100)]
    private float $basicRate = 20;

    #[ORM\Column]
    #[Assert\Positive]
    private float $basicRateLimit = 50270;

    #[ORM\Column]
    #[Assert\Range(min: 0, max: 100)]
    private float $higherRate = 40;

    #[ORM\Column]
    #[Assert\Positive]
    private float $higherRateLimit = 125140;

    #[ORM\Column]
    #[Assert\Range(min: 0, max: 100)]
    private float $additionalRate = 45;

    #[ORM\Column]
    private float $niPrimaryThreshold = 12570;

    #[ORM\Column]
    private float $niUpperEarningsLimit = 50270;

    #[ORM\Column]
    #[Assert\Range(min: 0, max: 100)]
    private float $niEmployeeRate = 8;

    #[ORM\Column]
    #[Assert\Range(min: 0, max: 100)]
    private float $niEmployeeUpperRate = 2;

    #[ORM\Column]
    #[Assert\Range(min: 0, max: 100)]
    private float $niEmployerRate = 15;

    #[ORM\Column]
    private float $employerNiThreshold = 5000;

    #[ORM\Column]
    private float $pensionLower = 6240;

    #[ORM\Column]
    private float $pensionUpper = 50270;

    #[ORM\Column]
    #[Assert\Range(min: 0, max: 100)]
    private float $pensionEmployeeRate = 5;

    #[ORM\Column]
    #[Assert\Range(min: 0, max: 100)]
    private float $pensionEmployerRate = 3;

    #[ORM\Column]
    private float $studentLoanPlan1Threshold = 26065;

    #[ORM\Column]
    private float $studentLoanPlan2Threshold = 28470;

    #[ORM\Column]
    private float $studentLoanPlan4Threshold = 32745;

    #[ORM\Column]
    private float $studentLoanPlan5Threshold = 25000;

    #[ORM\Column]
    private float $studentLoanPostgradThreshold = 21000;

    #[ORM\Column]
    #[Assert\Range(min: 0, max: 100)]
    private float $studentLoanRate = 9;

    #[ORM\Column]
    #[Assert\Range(min: 0, max: 100)]
    private float $studentLoanPostgradRate = 6;

    #[ORM\Column]
    #[Assert\Range(min: 0, max: 100)]
    private float $holidayAccrualRate = 12.07;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    private float $employmentAllowance = 10500;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $activatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getEffectiveFrom(): ?\DateTimeImmutable
    {
        return $this->effectiveFrom;
    }

    public function setEffectiveFrom(?\DateTimeImmutable $effectiveFrom): static
    {
        $this->effectiveFrom = $effectiveFrom;

        return $this;
    }

    public function getEffectiveTo(): ?\DateTimeImmutable
    {
        return $this->effectiveTo;
    }

    public function setEffectiveTo(?\DateTimeImmutable $effectiveTo): static
    {
        $this->effectiveTo = $effectiveTo;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function getPersonalAllowance(): float
    {
        return $this->personalAllowance;
    }

    public function setPersonalAllowance(float $value): static
    {
        $this->personalAllowance = $value;

        return $this;
    }

    public function getBasicRate(): float
    {
        return $this->basicRate;
    }

    public function setBasicRate(float $value): static
    {
        $this->basicRate = $value;

        return $this;
    }

    public function getBasicRateLimit(): float
    {
        return $this->basicRateLimit;
    }

    public function setBasicRateLimit(float $value): static
    {
        $this->basicRateLimit = $value;

        return $this;
    }

    public function getHigherRate(): float
    {
        return $this->higherRate;
    }

    public function setHigherRate(float $value): static
    {
        $this->higherRate = $value;

        return $this;
    }

    public function getHigherRateLimit(): float
    {
        return $this->higherRateLimit;
    }

    public function setHigherRateLimit(float $value): static
    {
        $this->higherRateLimit = $value;

        return $this;
    }

    public function getAdditionalRate(): float
    {
        return $this->additionalRate;
    }

    public function setAdditionalRate(float $value): static
    {
        $this->additionalRate = $value;

        return $this;
    }

    public function getNiPrimaryThreshold(): float
    {
        return $this->niPrimaryThreshold;
    }

    public function setNiPrimaryThreshold(float $value): static
    {
        $this->niPrimaryThreshold = $value;

        return $this;
    }

    public function getNiUpperEarningsLimit(): float
    {
        return $this->niUpperEarningsLimit;
    }

    public function setNiUpperEarningsLimit(float $value): static
    {
        $this->niUpperEarningsLimit = $value;

        return $this;
    }

    public function getNiEmployeeRate(): float
    {
        return $this->niEmployeeRate;
    }

    public function setNiEmployeeRate(float $value): static
    {
        $this->niEmployeeRate = $value;

        return $this;
    }

    public function getNiEmployeeUpperRate(): float
    {
        return $this->niEmployeeUpperRate;
    }

    public function setNiEmployeeUpperRate(float $value): static
    {
        $this->niEmployeeUpperRate = $value;

        return $this;
    }

    public function getNiEmployerRate(): float
    {
        return $this->niEmployerRate;
    }

    public function setNiEmployerRate(float $value): static
    {
        $this->niEmployerRate = $value;

        return $this;
    }

    public function getEmployerNiThreshold(): float
    {
        return $this->employerNiThreshold;
    }

    public function setEmployerNiThreshold(float $value): static
    {
        $this->employerNiThreshold = $value;

        return $this;
    }

    public function getPensionLower(): float
    {
        return $this->pensionLower;
    }

    public function setPensionLower(float $value): static
    {
        $this->pensionLower = $value;

        return $this;
    }

    public function getPensionUpper(): float
    {
        return $this->pensionUpper;
    }

    public function setPensionUpper(float $value): static
    {
        $this->pensionUpper = $value;

        return $this;
    }

    public function getPensionEmployeeRate(): float
    {
        return $this->pensionEmployeeRate;
    }

    public function setPensionEmployeeRate(float $value): static
    {
        $this->pensionEmployeeRate = $value;

        return $this;
    }

    public function getPensionEmployerRate(): float
    {
        return $this->pensionEmployerRate;
    }

    public function setPensionEmployerRate(float $value): static
    {
        $this->pensionEmployerRate = $value;

        return $this;
    }

    public function getStudentLoanPlan1Threshold(): float
    {
        return $this->studentLoanPlan1Threshold;
    }

    public function setStudentLoanPlan1Threshold(float $value): static
    {
        $this->studentLoanPlan1Threshold = $value;

        return $this;
    }

    public function getStudentLoanPlan2Threshold(): float
    {
        return $this->studentLoanPlan2Threshold;
    }

    public function setStudentLoanPlan2Threshold(float $value): static
    {
        $this->studentLoanPlan2Threshold = $value;

        return $this;
    }

    public function getStudentLoanPlan4Threshold(): float
    {
        return $this->studentLoanPlan4Threshold;
    }

    public function setStudentLoanPlan4Threshold(float $value): static
    {
        $this->studentLoanPlan4Threshold = $value;

        return $this;
    }

    public function getStudentLoanPlan5Threshold(): float
    {
        return $this->studentLoanPlan5Threshold;
    }

    public function setStudentLoanPlan5Threshold(float $value): static
    {
        $this->studentLoanPlan5Threshold = $value;

        return $this;
    }

    public function getStudentLoanPostgradThreshold(): float
    {
        return $this->studentLoanPostgradThreshold;
    }

    public function setStudentLoanPostgradThreshold(float $value): static
    {
        $this->studentLoanPostgradThreshold = $value;

        return $this;
    }

    public function getStudentLoanRate(): float
    {
        return $this->studentLoanRate;
    }

    public function setStudentLoanRate(float $value): static
    {
        $this->studentLoanRate = $value;

        return $this;
    }

    public function getStudentLoanPostgradRate(): float
    {
        return $this->studentLoanPostgradRate;
    }

    public function setStudentLoanPostgradRate(float $value): static
    {
        $this->studentLoanPostgradRate = $value;

        return $this;
    }

    public function getHolidayAccrualRate(): float
    {
        return $this->holidayAccrualRate;
    }

    public function setHolidayAccrualRate(float $value): static
    {
        $this->holidayAccrualRate = $value;

        return $this;
    }

    public function getEmploymentAllowance(): float
    {
        return $this->employmentAllowance;
    }

    public function setEmploymentAllowance(float $value): static
    {
        $this->employmentAllowance = $value;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getActivatedAt(): ?\DateTimeImmutable
    {
        return $this->activatedAt;
    }

    public function setActivatedAt(?\DateTimeImmutable $activatedAt): static
    {
        $this->activatedAt = $activatedAt;

        return $this;
    }

    /**
     * @return list<string>
     */
    public function validate(): array
    {
        $errors = [];

        if ('' === trim($this->name)) {
            $errors[] = 'A tax year name is required.';
        }

        if (null === $this->effectiveFrom || null === $this->effectiveTo) {
            $errors[] = 'Effective from and effective to dates are required.';
        } elseif ($this->effectiveFrom > $this->effectiveTo) {
            $errors[] = 'The effective start date must be before the end date.';
        }

        if ($this->basicRateLimit <= $this->personalAllowance) {
            $errors[] = 'The basic rate limit must be above the personal allowance.';
        }

        if ($this->higherRateLimit <= $this->basicRateLimit) {
            $errors[] = 'The higher rate limit must be above the basic rate limit.';
        }

        if ($this->niUpperEarningsLimit <= $this->niPrimaryThreshold) {
            $errors[] = 'The NI upper earnings limit must be above the primary threshold.';
        }

        if ($this->pensionUpper <= $this->pensionLower) {
            $errors[] = 'The pension upper earnings limit must be above the lower limit.';
        }

        if ($this->employerNiThreshold < 0 || $this->employmentAllowance < 0) {
            $errors[] = 'Thresholds and allowances cannot be negative.';
        }

        return $errors;
    }

    public function getStudentLoanThreshold(string $plan): ?float
    {
        return match ($plan) {
            Employee::LOAN_PLAN_1 => $this->studentLoanPlan1Threshold,
            Employee::LOAN_PLAN_2 => $this->studentLoanPlan2Threshold,
            Employee::LOAN_PLAN_4 => $this->studentLoanPlan4Threshold,
            Employee::LOAN_PLAN_5 => $this->studentLoanPlan5Threshold,
            Employee::LOAN_POSTGRAD => $this->studentLoanPostgradThreshold,
            default => null,
        };
    }

    public function getStudentLoanRateForPlan(string $plan): float
    {
        return Employee::LOAN_POSTGRAD === $plan ? $this->studentLoanPostgradRate : $this->studentLoanRate;
    }
}
