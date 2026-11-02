<?php

namespace App\Controller;

use App\Entity\Employee;
use App\Entity\PayrollRun;
use App\Entity\Payslip;
use App\Form\PayrollRunType;
use App\Repository\EmployeeRepository;
use App\Repository\PayrollRunRepository;
use App\Service\AuditLogger;
use App\Service\HolidayService;
use App\Service\PayrollCalculator;
use App\Service\PayrollResult;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/payroll')]
class PayrollController extends AbstractController
{
    public function __construct(
        private readonly PayrollCalculator $calculator,
        private readonly HolidayService $holidayService,
    ) {
    }

    #[Route('', name: 'app_payroll_index', methods: ['GET'])]
    public function index(PayrollRunRepository $runs): Response
    {
        return $this->render('payroll/index.html.twig', [
            'runs' => $runs->findRecent(100),
        ]);
    }

    #[Route('/new', name: 'app_payroll_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        EmployeeRepository $employees,
        AuditLogger $audit,
    ): Response {
        if (null === $this->getActiveTaxYear($entityManager)) {
            $this->addFlash('error', 'Create and activate a tax year before starting payroll.');

            return $this->redirectToRoute('app_tax_year_new');
        }

        $run = new PayrollRun();
        $run->setTaxYear($this->getActiveTaxYear($entityManager));
        $run->setPeriodStart(new \DateTimeImmutable('first day of this month'));
        $run->setPeriodEnd(new \DateTimeImmutable('last day of this month'));
        $run->setPayDate(new \DateTimeImmutable('last day of this month'));

        $form = $this->createForm(PayrollRunType::class, $run);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($run->getPeriodStart() > $run->getPeriodEnd()) {
                $this->addFlash('error', 'The period start date must be before the period end date.');
            } else {
                $entityManager->persist($run);

                foreach ($employees->findActiveOrdered() as $employee) {
                    $payslip = new Payslip();
                    $payslip->setEmployee($employee);
                    $payslip->setHoursWorked($this->defaultHoursFor($employee, $run->getFrequency()));
                    $this->applyResult($payslip, $this->calculator->calculate(
                        $employee,
                        $payslip->getHoursWorked(),
                        0,
                        0,
                        $run->getTaxYear(),
                        $run->getFrequency(),
                    ));
                    $run->addPayslip($payslip);
                }

                $run->recalculateTotals();
                $entityManager->flush();
                $audit->log('payroll.created', 'PayrollRun', $run->getId(), sprintf('Created payroll run for %s to %s.', $run->getPeriodStart()->format('j M Y'), $run->getPeriodEnd()->format('j M Y')));

                $this->addFlash('success', 'Payroll run created. Review hours and finalise when ready.');

                return $this->redirectToRoute('app_payroll_show', ['id' => $run->getId()]);
            }
        }

        return $this->render('payroll/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_payroll_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(PayrollRun $run, EmployeeRepository $employees): Response
    {
        $existing = [];
        foreach ($run->getPayslips() as $payslip) {
            $existing[$payslip->getEmployee()->getId()] = true;
        }

        $missing = array_values(array_filter(
            $employees->findActiveOrdered(),
            static fn (Employee $employee) => !isset($existing[$employee->getId()])
        ));

        return $this->render('payroll/show.html.twig', [
            'run' => $run,
            'missingEmployees' => $missing,
        ]);
    }

    #[Route('/{id}/generate', name: 'app_payroll_generate', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function generate(Request $request, PayrollRun $run, EntityManagerInterface $entityManager, EmployeeRepository $employees, AuditLogger $audit): Response
    {
        $this->assertDraft($run);
        $this->assertCsrf($request, 'payroll_generate_'.$run->getId());

        $existing = [];
        foreach ($run->getPayslips() as $payslip) {
            $existing[$payslip->getEmployee()->getId()] = true;
        }

        $added = 0;
        foreach ($employees->findActiveOrdered() as $employee) {
            if (isset($existing[$employee->getId()])) {
                continue;
            }

            $payslip = new Payslip();
            $payslip->setEmployee($employee);
            $payslip->setHoursWorked($this->defaultHoursFor($employee, $run->getFrequency()));
            $this->applyResult($payslip, $this->calculator->calculate(
                $employee,
                $payslip->getHoursWorked(),
                0,
                0,
                $run->getTaxYear(),
                $run->getFrequency(),
            ));
            $run->addPayslip($payslip);
            ++$added;
        }

        $run->recalculateTotals();
        $entityManager->flush();

        if ($added > 0) {
            $audit->log('payroll.payslips_generated', 'PayrollRun', $run->getId(), sprintf('Added %d employee payslips to the run.', $added));
            $this->addFlash('success', sprintf('Added %d employee payslips.', $added));
        } else {
            $this->addFlash('info', 'Every active employee already has a payslip on this run.');
        }

        return $this->redirectToRoute('app_payroll_show', ['id' => $run->getId()]);
    }

    #[Route('/{run}/payslip/{payslip}', name: 'app_payroll_update_payslip', methods: ['POST'], requirements: ['run' => '\d+', 'payslip' => '\d+'])]
    public function updatePayslip(
        Request $request,
        PayrollRun $run,
        Payslip $payslip,
        EntityManagerInterface $entityManager,
        AuditLogger $audit,
    ): Response {
        $this->assertDraft($run);

        if ($payslip->getPayrollRun()?->getId() !== $run->getId()) {
            throw $this->createNotFoundException('That payslip does not belong to this payroll run.');
        }

        $this->assertCsrf($request, 'payslip_update_'.$payslip->getId());

        $hours = max(0, (float) $request->request->get('hours_worked', 0));
        $overtime = max(0, (float) $request->request->get('overtime_hours', 0));
        $bonus = max(0, (float) $request->request->get('bonus', 0));

        $payslip->setHoursWorked($hours);
        $payslip->setOvertimeHours($overtime);
        $payslip->setBonus($bonus);

        $result = $this->calculator->calculate(
            $payslip->getEmployee(),
            $hours,
            $overtime,
            $bonus,
            $run->getTaxYear(),
            $run->getFrequency(),
        );

        $this->applyResult($payslip, $result);
        $payslip->touch();

        $run->recalculateTotals();
        $entityManager->flush();
        $audit->log('payroll.payslip_updated', 'Payslip', $payslip->getId(), sprintf('Updated hours for %s.', $payslip->getEmployee()->getFullName()));

        $this->addFlash('success', sprintf('Payslip for %s recalculated.', $payslip->getEmployee()->getFullName()));

        return $this->redirectToRoute('app_payroll_show', ['id' => $run->getId()]);
    }

    #[Route('/{id}/finalise', name: 'app_payroll_finalise', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function finalise(Request $request, PayrollRun $run, EntityManagerInterface $entityManager, AuditLogger $audit): Response
    {
        $this->assertDraft($run);
        $this->assertCsrf($request, 'payroll_finalise_'.$run->getId());

        if ($run->getPayslips()->isEmpty()) {
            $this->addFlash('error', 'Add at least one payslip before finalising this payroll run.');

            return $this->redirectToRoute('app_payroll_show', ['id' => $run->getId()]);
        }

        $run->recalculateTotals();
        $run->setStatus(PayrollRun::STATUS_FINALISED);
        $run->setFinalisedAt(new \DateTimeImmutable());

        $accrued = $this->holidayService->accrueForPayrollRun($run);

        $entityManager->flush();
        $audit->log('payroll.finalised', 'PayrollRun', $run->getId(), sprintf('Finalised payroll run %s. %d holiday accrual entries created.', $run->getPaymentReference(), $accrued));

        $this->addFlash('success', 'Payroll finalised. Payslips and holiday accrual are now available.');

        return $this->redirectToRoute('app_payroll_show', ['id' => $run->getId()]);
    }

    #[Route('/{id}/delete', name: 'app_payroll_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, PayrollRun $run, EntityManagerInterface $entityManager, AuditLogger $audit): Response
    {
        $this->assertDraft($run);
        $this->assertCsrf($request, 'payroll_delete_'.$run->getId());

        $id = $run->getId();
        $reference = $run->getPaymentReference();
        $entityManager->remove($run);
        $entityManager->flush();

        $audit->log('payroll.deleted', 'PayrollRun', $id, sprintf('Deleted draft payroll run %s.', $reference));

        $this->addFlash('success', 'Draft payroll run deleted.');

        return $this->redirectToRoute('app_payroll_index');
    }

    private function applyResult(Payslip $payslip, PayrollResult $result): void
    {
        $employee = $payslip->getEmployee();

        $payslip
            ->setTaxCode($employee->getTaxCode())
            ->setNiCategory($employee->getNiCategory())
            ->setHourlyRate($result->hourlyRate)
            ->setBasicPay($result->basicPay)
            ->setOvertimePay($result->overtimePay)
            ->setBonus($result->bonus)
            ->setGrossPay($result->grossPay)
            ->setPaye($result->paye)
            ->setEmployeeNi($result->employeeNi)
            ->setEmployerNi($result->employerNi)
            ->setPensionEmployee($result->pensionEmployee)
            ->setPensionEmployer($result->pensionEmployer)
            ->setStudentLoan($result->studentLoan)
            ->setNetPay($result->netPay)
            ->setEmployerCost($result->employerCost);
    }

    private function defaultHoursFor(Employee $employee, string $frequency): float
    {
        if (Employee::FREQUENCY_WEEKLY === $frequency) {
            return round($employee->getContractedWeeklyHours(), 2);
        }

        return round($employee->getContractedWeeklyHours() * 52 / 12, 2);
    }

    private function assertDraft(PayrollRun $run): void
    {
        if (!$run->isDraft()) {
            throw $this->createAccessDeniedException('Finalised payroll runs cannot be changed.');
        }
    }

    private function assertCsrf(Request $request, string $tokenId): void
    {
        if (!$this->isCsrfTokenValid($tokenId, (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }
    }

    private function getActiveTaxYear(EntityManagerInterface $entityManager): ?\App\Entity\TaxYear
    {
        return $entityManager->getRepository(\App\Entity\TaxYear::class)->findOneBy(['active' => true]);
    }
}
