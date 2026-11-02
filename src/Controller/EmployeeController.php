<?php

namespace App\Controller;

use App\Entity\Employee;
use App\Form\EmployeeType;
use App\Repository\EmployeeRepository;
use App\Repository\HolidayBookingRepository;
use App\Repository\HolidayLedgerEntryRepository;
use App\Repository\PayslipRepository;
use App\Service\AuditLogger;
use App\Service\HolidayService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/employees')]
class EmployeeController extends AbstractController
{
    #[Route('', name: 'app_employees', methods: ['GET'])]
    public function index(Request $request, EmployeeRepository $employees, HolidayService $holidayService): Response
    {
        $query = (string) $request->query->get('q', '');
        $results = $employees->search($query);

        $balances = [];
        foreach ($results as $employee) {
            $balances[$employee->getId()] = $holidayService->getBalance($employee);
        }

        return $this->render('employee/index.html.twig', [
            'employees' => $results,
            'query' => $query,
            'balances' => $balances,
        ]);
    }

    #[Route('/new', name: 'app_employee_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, AuditLogger $audit): Response
    {
        $employee = new Employee();
        $form = $this->createForm(EmployeeType::class, $employee);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($employee);
            $entityManager->flush();

            $audit->log('employee.created', 'Employee', $employee->getId(), sprintf('Created employee %s.', $employee->getFullName()));

            $this->addFlash('success', 'Employee record created.');

            return $this->redirectToRoute('app_employee_show', ['id' => $employee->getId()]);
        }

        return $this->render('employee/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_employee_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(
        Employee $employee,
        HolidayService $holidayService,
        HolidayLedgerEntryRepository $ledger,
        HolidayBookingRepository $bookings,
        PayslipRepository $payslips,
    ): Response {
        return $this->render('employee/show.html.twig', [
            'employee' => $employee,
            'balance' => $holidayService->getBalance($employee),
            'accrued' => $holidayService->getAccrued($employee),
            'taken' => $holidayService->getTaken($employee),
            'ledger' => $ledger->findByEmployeeOrdered($employee->getId()),
            'bookings' => $bookings->findByEmployeeOrdered($employee->getId()),
            'payslips' => $payslips->findByEmployeeOrdered($employee->getId()),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_employee_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, Employee $employee, EntityManagerInterface $entityManager, AuditLogger $audit): Response
    {
        $form = $this->createForm(EmployeeType::class, $employee);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $audit->log('employee.updated', 'Employee', $employee->getId(), sprintf('Updated employee %s.', $employee->getFullName()));

            $this->addFlash('success', 'Employee record updated.');

            return $this->redirectToRoute('app_employee_show', ['id' => $employee->getId()]);
        }

        return $this->render('employee/edit.html.twig', [
            'form' => $form,
            'employee' => $employee,
        ]);
    }

    #[Route('/{id}/toggle', name: 'app_employee_toggle', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function toggle(Request $request, Employee $employee, EntityManagerInterface $entityManager, AuditLogger $audit): Response
    {
        if (!$this->isCsrfTokenValid('toggle_employee_'.$employee->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $employee->setStatus($employee->isActive() ? Employee::STATUS_INACTIVE : Employee::STATUS_ACTIVE);
        $entityManager->flush();

        $audit->log('employee.status_changed', 'Employee', $employee->getId(), sprintf('Employee %s marked %s.', $employee->getFullName(), $employee->getStatus()));

        $this->addFlash('success', sprintf('%s is now %s.', $employee->getFullName(), $employee->getStatus()));

        return $this->redirectToRoute('app_employee_show', ['id' => $employee->getId()]);
    }
}
