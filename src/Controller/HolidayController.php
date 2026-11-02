<?php

namespace App\Controller;

use App\Entity\Employee;
use App\Entity\HolidayBooking;
use App\Form\HolidayAdjustmentType;
use App\Form\HolidayBookingType;
use App\Repository\EmployeeRepository;
use App\Repository\HolidayBookingRepository;
use App\Repository\HolidayLedgerEntryRepository;
use App\Service\AuditLogger;
use App\Service\HolidayService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/holidays')]
class HolidayController extends AbstractController
{
    public function __construct(
        private readonly HolidayService $holidayService,
    ) {
    }

    #[Route('', name: 'app_holidays', methods: ['GET'])]
    public function index(EmployeeRepository $employees, HolidayBookingRepository $bookings): Response
    {
        $rows = [];
        foreach ($employees->findActiveOrdered() as $employee) {
            $rows[] = [
                'employee' => $employee,
                'entitlement' => $employee->getHolidayEntitlementHours(),
                'accrued' => $this->holidayService->getAccrued($employee),
                'taken' => $this->holidayService->getTaken($employee),
                'balance' => $this->holidayService->getBalance($employee),
            ];
        }

        return $this->render('holiday/index.html.twig', [
            'rows' => $rows,
            'upcomingBookings' => $bookings->findUpcoming(),
        ]);
    }

    #[Route('/{id}', name: 'app_holiday_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(
        Employee $employee,
        HolidayLedgerEntryRepository $ledger,
        HolidayBookingRepository $bookings,
        Request $request,
    ): Response {
        $booking = new HolidayBooking();
        $booking->setEmployee($employee);
        $booking->setStartDate(new \DateTimeImmutable('today'));
        $booking->setEndDate(new \DateTimeImmutable('today'));
        $booking->setHours($this->holidayService->estimateWorkingHours($employee, $booking->getStartDate(), $booking->getEndDate()));

        $bookingForm = $this->createForm(HolidayBookingType::class, $booking, [
            'action' => $this->generateUrl('app_holiday_book', ['id' => $employee->getId()]),
        ]);

        $adjustmentForm = $this->createForm(HolidayAdjustmentType::class, null, [
            'action' => $this->generateUrl('app_holiday_adjust', ['id' => $employee->getId()]),
        ]);

        return $this->render('holiday/show.html.twig', [
            'employee' => $employee,
            'balance' => $this->holidayService->getBalance($employee),
            'accrued' => $this->holidayService->getAccrued($employee),
            'taken' => $this->holidayService->getTaken($employee),
            'ledger' => $ledger->findByEmployeeOrdered($employee->getId()),
            'bookings' => $bookings->findByEmployeeOrdered($employee->getId()),
            'bookingForm' => $bookingForm,
            'adjustmentForm' => $adjustmentForm,
        ]);
    }

    #[Route('/{id}/book', name: 'app_holiday_book', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function book(Request $request, Employee $employee, EntityManagerInterface $entityManager, AuditLogger $audit): Response
    {
        $booking = (new HolidayBooking())->setEmployee($employee);
        $form = $this->createForm(HolidayBookingType::class, $booking);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }

            return $this->redirectToRoute('app_holiday_show', ['id' => $employee->getId()]);
        }

        if ($booking->getEndDate() < $booking->getStartDate()) {
            $this->addFlash('error', 'The leave end date cannot be before the start date.');

            return $this->redirectToRoute('app_holiday_show', ['id' => $employee->getId()]);
        }

        $balance = $this->holidayService->getBalance($employee);
        $this->holidayService->book(
            $employee,
            $booking->getStartDate(),
            $booking->getEndDate(),
            $booking->getHours(),
            $booking->getNote(),
        );

        $entityManager->flush();
        $audit->log('holiday.booked', 'Employee', $employee->getId(), sprintf('Booked %.2f holiday hours for %s.', $booking->getHours(), $employee->getFullName()));

        if ($booking->getHours() > $balance) {
            $this->addFlash('warning', 'This booking exceeds the current holiday balance. It is now awaiting further accrual.');
        } else {
            $this->addFlash('success', 'Holiday booked.');
        }

        return $this->redirectToRoute('app_holiday_show', ['id' => $employee->getId()]);
    }

    #[Route('/{id}/adjust', name: 'app_holiday_adjust', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function adjust(Request $request, Employee $employee, EntityManagerInterface $entityManager, AuditLogger $audit): Response
    {
        $form = $this->createForm(HolidayAdjustmentType::class);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->addFlash('error', 'Enter a valid adjustment and reason.');

            return $this->redirectToRoute('app_holiday_show', ['id' => $employee->getId()]);
        }

        $hours = (float) $form->get('hours')->getData();

        if (0.0 === $hours) {
            $this->addFlash('error', 'The adjustment must be a non-zero number of hours.');

            return $this->redirectToRoute('app_holiday_show', ['id' => $employee->getId()]);
        }

        $this->holidayService->adjust($employee, $hours, (string) $form->get('description')->getData());
        $entityManager->flush();
        $audit->log('holiday.adjusted', 'Employee', $employee->getId(), sprintf('Manual holiday adjustment of %.2f hours for %s.', $hours, $employee->getFullName()));

        $this->addFlash('success', 'Holiday balance adjusted.');

        return $this->redirectToRoute('app_holiday_show', ['id' => $employee->getId()]);
    }

    #[Route('/{id}/bookings/{booking}/cancel', name: 'app_holiday_cancel', methods: ['POST'], requirements: ['id' => '\d+', 'booking' => '\d+'])]
    public function cancel(Request $request, Employee $employee, HolidayBooking $booking, EntityManagerInterface $entityManager, AuditLogger $audit): Response
    {
        $this->assertBookingOwnership($employee, $booking);
        $this->assertCsrf($request, 'holiday_cancel_'.$booking->getId());

        $this->holidayService->cancel($booking);
        $entityManager->flush();
        $audit->log('holiday.cancelled', 'Employee', $employee->getId(), sprintf('Cancelled holiday booking for %s. Balance restored.', $employee->getFullName()));

        $this->addFlash('success', 'Booking cancelled and holiday balance restored.');

        return $this->redirectToRoute('app_holiday_show', ['id' => $employee->getId()]);
    }

    #[Route('/{id}/bookings/{booking}/taken', name: 'app_holiday_taken', methods: ['POST'], requirements: ['id' => '\d+', 'booking' => '\d+'])]
    public function taken(Request $request, Employee $employee, HolidayBooking $booking, EntityManagerInterface $entityManager, AuditLogger $audit): Response
    {
        $this->assertBookingOwnership($employee, $booking);
        $this->assertCsrf($request, 'holiday_taken_'.$booking->getId());

        $this->holidayService->markTaken($booking);
        $entityManager->flush();
        $audit->log('holiday.taken', 'Employee', $employee->getId(), sprintf('Marked holiday as taken for %s.', $employee->getFullName()));

        $this->addFlash('success', 'Booking marked as taken.');

        return $this->redirectToRoute('app_holiday_show', ['id' => $employee->getId()]);
    }

    private function assertBookingOwnership(Employee $employee, HolidayBooking $booking): void
    {
        if ($booking->getEmployee()?->getId() !== $employee->getId()) {
            throw $this->createNotFoundException('Booking not found for this employee.');
        }
    }

    private function assertCsrf(Request $request, string $tokenId): void
    {
        if (!$this->isCsrfTokenValid($tokenId, (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }
    }
}
