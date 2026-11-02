<?php

namespace App\Controller;

use App\Entity\PayrollRun;
use App\Repository\PayrollRunRepository;
use App\Repository\TaxYearRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/hmrc')]
class HmrcController extends AbstractController
{
    #[Route('', name: 'app_hmrc_index', methods: ['GET'])]
    public function index(PayrollRunRepository $runs, TaxYearRepository $taxYears): Response
    {
        $finalised = $runs->findFinalisedOrdered();

        $taxYearSummaries = [];
        foreach ($taxYears->findAllOrdered() as $taxYear) {
            $yearRuns = array_values(array_filter(
                $finalised,
                static fn (PayrollRun $run) => $run->getTaxYear()?->getId() === $taxYear->getId()
            ));

            if ([] === $yearRuns) {
                continue;
            }

            $taxYearSummaries[] = [
                'taxYear' => $taxYear,
                'runs' => $yearRuns,
                'paye' => array_sum(array_map(static fn (PayrollRun $r) => $r->getPayeTotal(), $yearRuns)),
                'employeeNi' => array_sum(array_map(static fn (PayrollRun $r) => $r->getEmployeeNiTotal(), $yearRuns)),
                'employerNi' => array_sum(array_map(static fn (PayrollRun $r) => $r->getEmployerNiTotal(), $yearRuns)),
                'allowance' => array_sum(array_map(static fn (PayrollRun $r) => $r->getEmploymentAllowanceApplied(), $yearRuns)),
                'studentLoan' => array_sum(array_map(static fn (PayrollRun $r) => $r->getStudentLoanTotal(), $yearRuns)),
                'total' => array_sum(array_map(static fn (PayrollRun $r) => $r->getHmrcLiabilityTotal(), $yearRuns)),
            ];
        }

        return $this->render('hmrc/index.html.twig', [
            'runs' => $finalised,
            'taxYearSummaries' => $taxYearSummaries,
            'totalLiability' => array_sum(array_map(static fn (PayrollRun $r) => $r->getHmrcLiabilityTotal(), $finalised)),
        ]);
    }

    #[Route('/{id}', name: 'app_hmrc_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(PayrollRun $run): Response
    {
        return $this->render('hmrc/show.html.twig', [
            'run' => $run,
        ]);
    }
}
