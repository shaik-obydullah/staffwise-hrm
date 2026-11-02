<?php

namespace App\Controller;

use App\Entity\Employee;
use App\Entity\PayrollRun;
use App\Repository\HolidayBookingRepository;
use App\Repository\PayrollRunRepository;
use App\Repository\EmployeeRepository;
use App\Repository\TaxYearRepository;
use App\Service\HolidayService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard', methods: ['GET'])]
    public function index(
        EmployeeRepository $employees,
        PayrollRunRepository $payrollRuns,
        TaxYearRepository $taxYears,
        HolidayBookingRepository $bookings,
        HolidayService $holidayService,
    ): Response {
        $activeEmployees = $employees->findActiveOrdered();
        $recentRuns = $payrollRuns->findRecent(5);
        $draftRuns = $payrollRuns->findDraftOrdered();
        $latestFinalised = $payrollRuns->findLatestFinalised();
        $activeTaxYear = $taxYears->findActive();

        $holidayAttention = [];
        foreach ($activeEmployees as $employee) {
            $balance = $holidayService->getBalance($employee);
            $dailyHours = $employee->getContractedWeeklyHours() / 5;

            if ($balance < $dailyHours) {
                $holidayAttention[] = ['employee' => $employee, 'balance' => $balance];
            }
        }
        usort($holidayAttention, static fn (array $a, array $b) => $a['balance'] <=> $b['balance']);

        $nextPayDate = null;
        if ([] !== $draftRuns) {
            $nextPayDate = $draftRuns[0]->getPayDate();
        } elseif ([] !== $recentRuns) {
            $latest = $recentRuns[0];
            $nextPayDate = Employee::FREQUENCY_WEEKLY === $latest->getFrequency()
                ? $latest->getPayDate()?->modify('+7 days')
                : $latest->getPayDate()?->modify('+1 month');
        }

        return $this->render('dashboard/index.html.twig', [
            'headcount' => $employees->countActive(),
            'activeEmployees' => array_slice($activeEmployees, 0, 5),
            'recentRuns' => $recentRuns,
            'draftRuns' => $draftRuns,
            'latestFinalised' => $latestFinalised,
            'activeTaxYear' => $activeTaxYear,
            'nextPayDate' => $nextPayDate,
            'holidayAttention' => array_slice($holidayAttention, 0, 5),
            'upcomingBookings' => $bookings->findUpcoming(),
            'activeTaxYearLiability' => $activeTaxYear ? $payrollRuns->getTotalHmrcLiabilityForTaxYear($activeTaxYear->getId()) : 0.0,
        ]);
    }
}
