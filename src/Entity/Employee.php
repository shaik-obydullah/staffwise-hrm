<?php

namespace App\Entity;

use App\Repository\EmployeeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EmployeeRepository::class)]
#[ORM\Table(name: 'employee')]
class Employee
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    public const FREQUENCY_WEEKLY = 'weekly';
    public const FREQUENCY_MONTHLY = 'monthly';

    public const LOAN_NONE = 'none';
    public const LOAN_PLAN_1 = 'plan1';
    public const LOAN_PLAN_2 = 'plan2';
    public const LOAN_PLAN_4 = 'plan4';
    public const LOAN_PLAN_5 = 'plan5';
    public const LOAN_POSTGRAD = 'postgrad';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private string $firstName = '';

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private string $lastName = '';

    #[ORM\Column(length: 180, nullable: true)]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column(length: 40, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Regex(pattern: '/^[A-Za-z]{2}\s?\d{2}\s?\d{2}\s?\d{2}\s?[A-Za-z]?$/i', message: 'Enter a valid National Insurance number.')]
    private ?string $niNumber = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $jobTitle = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $department = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $employmentStartDate = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $employmentEndDate = null;

    #[ORM\Column(length: 20)]
    private string $status = self::STATUS_ACTIVE;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    private float $hourlyRate = 0.0;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank]
    private string $taxCode = '1257L';

    #[ORM\Column(length: 2)]
    private string $niCategory = 'A';

    #[ORM\Column]
    private bool $pensionOptIn = false;

    #[ORM\Column(nullable: true)]
    #[Assert\Range(min: 0, max: 100)]
    private ?float $pensionEmployeeRate = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Range(min: 0, max: 100)]
    private ?float $pensionEmployerRate = null;

    #[ORM\Column(length: 20)]
    private string $studentLoanPlan = self::LOAN_NONE;

    #[ORM\Column(length: 20)]
    private string $payFrequency = self::FREQUENCY_MONTHLY;

    #[ORM\Column]
    #[Assert\Positive]
    private float $contractedWeeklyHours = 37.5;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    private float $holidayEntitlementWeeks = 5.6;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    /** @var Collection<int, Payslip> */
    #[ORM\OneToMany(mappedBy: 'employee', targetEntity: Payslip::class)]
    private Collection $payslips;

    /** @var Collection<int, HolidayLedgerEntry> */
    #[ORM\OneToMany(mappedBy: 'employee', targetEntity: HolidayLedgerEntry::class)]
    private Collection $holidayLedgerEntries;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->payslips = new ArrayCollection();
        $this->holidayLedgerEntries = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFullName(): string
    {
        return trim($this->firstName.' '.$this->lastName);
    }

    public function getInitials(): string
    {
        return strtoupper(mb_substr($this->firstName, 0, 1).mb_substr($this->lastName, 0, 1));
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getNiNumber(): ?string
    {
        return $this->niNumber;
    }

    public function setNiNumber(?string $niNumber): static
    {
        $this->niNumber = $niNumber;

        return $this;
    }

    public function getJobTitle(): ?string
    {
        return $this->jobTitle;
    }

    public function setJobTitle(?string $jobTitle): static
    {
        $this->jobTitle = $jobTitle;

        return $this;
    }

    public function getDepartment(): ?string
    {
        return $this->department;
    }

    public function setDepartment(?string $department): static
    {
        $this->department = $department;

        return $this;
    }

    public function getEmploymentStartDate(): ?\DateTimeImmutable
    {
        return $this->employmentStartDate;
    }

    public function setEmploymentStartDate(?\DateTimeImmutable $employmentStartDate): static
    {
        $this->employmentStartDate = $employmentStartDate;

        return $this;
    }

    public function getEmploymentEndDate(): ?\DateTimeImmutable
    {
        return $this->employmentEndDate;
    }

    public function setEmploymentEndDate(?\DateTimeImmutable $employmentEndDate): static
    {
        $this->employmentEndDate = $employmentEndDate;

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

    public function isActive(): bool
    {
        return self::STATUS_ACTIVE === $this->status;
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

    public function isPensionOptIn(): bool
    {
        return $this->pensionOptIn;
    }

    public function setPensionOptIn(bool $pensionOptIn): static
    {
        $this->pensionOptIn = $pensionOptIn;

        return $this;
    }

    public function getPensionEmployeeRate(): ?float
    {
        return $this->pensionEmployeeRate;
    }

    public function setPensionEmployeeRate(?float $pensionEmployeeRate): static
    {
        $this->pensionEmployeeRate = $pensionEmployeeRate;

        return $this;
    }

    public function getPensionEmployerRate(): ?float
    {
        return $this->pensionEmployerRate;
    }

    public function setPensionEmployerRate(?float $pensionEmployerRate): static
    {
        $this->pensionEmployerRate = $pensionEmployerRate;

        return $this;
    }

    public function getStudentLoanPlan(): string
    {
        return $this->studentLoanPlan;
    }

    public function setStudentLoanPlan(string $studentLoanPlan): static
    {
        $this->studentLoanPlan = $studentLoanPlan;

        return $this;
    }

    public function getPayFrequency(): string
    {
        return $this->payFrequency;
    }

    public function setPayFrequency(string $payFrequency): static
    {
        $this->payFrequency = $payFrequency;

        return $this;
    }

    public function getContractedWeeklyHours(): float
    {
        return $this->contractedWeeklyHours;
    }

    public function setContractedWeeklyHours(float $contractedWeeklyHours): static
    {
        $this->contractedWeeklyHours = $contractedWeeklyHours;

        return $this;
    }

    public function getHolidayEntitlementWeeks(): float
    {
        return $this->holidayEntitlementWeeks;
    }

    public function setHolidayEntitlementWeeks(float $holidayEntitlementWeeks): static
    {
        $this->holidayEntitlementWeeks = $holidayEntitlementWeeks;

        return $this;
    }

    public function getHolidayEntitlementHours(): float
    {
        return round($this->holidayEntitlementWeeks * $this->contractedWeeklyHours, 2);
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /** @return Collection<int, Payslip> */
    public function getPayslips(): Collection
    {
        return $this->payslips;
    }

    /** @return Collection<int, HolidayLedgerEntry> */
    public function getHolidayLedgerEntries(): Collection
    {
        return $this->holidayLedgerEntries;
    }
}
