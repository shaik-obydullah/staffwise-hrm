<?php

namespace App\Controller;

use App\Entity\Payslip;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PayslipController extends AbstractController
{
    #[Route('/payslips/{id}', name: 'app_payslip_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Payslip $payslip): Response
    {
        return $this->render('payslip/show.html.twig', [
            'payslip' => $payslip,
            'run' => $payslip->getPayrollRun(),
        ]);
    }

    #[Route('/payslips/{id}/print', name: 'app_payslip_print', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function print(Payslip $payslip): Response
    {
        return $this->render('payslip/print.html.twig', [
            'payslip' => $payslip,
            'run' => $payslip->getPayrollRun(),
        ]);
    }
}
