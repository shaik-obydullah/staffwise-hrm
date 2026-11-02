<?php

namespace App\Controller;

use App\Entity\TaxYear;
use App\Form\TaxYearType;
use App\Repository\TaxYearRepository;
use App\Service\AuditLogger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/tax-years')]
class TaxYearController extends AbstractController
{
    #[Route('', name: 'app_tax_years', methods: ['GET'])]
    public function index(TaxYearRepository $taxYears): Response
    {
        return $this->render('tax_year/index.html.twig', [
            'taxYears' => $taxYears->findAllOrdered(),
        ]);
    }

    #[Route('/new', name: 'app_tax_year_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, TaxYearRepository $taxYears, AuditLogger $audit): Response
    {
        $taxYear = new TaxYear();
        $taxYear->setEffectiveFrom(new \DateTimeImmutable('2026-04-06'));
        $taxYear->setEffectiveTo(new \DateTimeImmutable('2027-04-05'));

        $form = $this->createForm(TaxYearType::class, $taxYear);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $errors = $taxYear->validate();

            if ([] !== $errors) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error);
                }
            } else {
                if ($taxYear->isActive()) {
                    $this->deactivateAll($taxYears);
                    $taxYear->setActivatedAt(new \DateTimeImmutable());
                }

                $entityManager->persist($taxYear);
                $entityManager->flush();
                $audit->log('tax_year.created', 'TaxYear', $taxYear->getId(), sprintf('Created tax year %s%s.', $taxYear->getName(), $taxYear->isActive() ? ' and activated it' : ''));

                $this->addFlash('success', 'Tax year created.');

                return $this->redirectToRoute('app_tax_years');
            }
        }

        return $this->render('tax_year/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_tax_year_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, TaxYear $taxYear, EntityManagerInterface $entityManager, TaxYearRepository $taxYears, AuditLogger $audit): Response
    {
        $wasActive = $taxYear->isActive();
        $form = $this->createForm(TaxYearType::class, $taxYear);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $errors = $taxYear->validate();

            if ([] !== $errors) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error);
                }
            } else {
                if ($taxYear->isActive()) {
                    $this->deactivateAll($taxYears, $taxYear);

                    if (!$wasActive) {
                        $taxYear->setActivatedAt(new \DateTimeImmutable());
                    }
                }

                $entityManager->flush();
                $audit->log('tax_year.updated', 'TaxYear', $taxYear->getId(), sprintf('Updated tax year %s%s.', $taxYear->getName(), $taxYear->isActive() ? ' (active)' : ''));

                $this->addFlash('success', 'Tax year updated.');

                return $this->redirectToRoute('app_tax_years');
            }
        }

        return $this->render('tax_year/edit.html.twig', [
            'form' => $form,
            'taxYear' => $taxYear,
        ]);
    }

    #[Route('/{id}/activate', name: 'app_tax_year_activate', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function activate(Request $request, TaxYear $taxYear, EntityManagerInterface $entityManager, TaxYearRepository $taxYears, AuditLogger $audit): Response
    {
        if (!$this->isCsrfTokenValid('activate_tax_year_'.$taxYear->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $errors = $taxYear->validate();

        if ([] !== $errors) {
            foreach ($errors as $error) {
                $this->addFlash('error', $error);
            }

            return $this->redirectToRoute('app_tax_year_edit', ['id' => $taxYear->getId()]);
        }

        $this->deactivateAll($taxYears, $taxYear);
        $taxYear->setActive(true)->setActivatedAt(new \DateTimeImmutable());
        $entityManager->flush();

        $audit->log('tax_year.activated', 'TaxYear', $taxYear->getId(), sprintf('Activated tax year %s.', $taxYear->getName()));

        $this->addFlash('success', sprintf('Tax year %s is now active.', $taxYear->getName()));

        return $this->redirectToRoute('app_tax_years');
    }

    private function deactivateAll(TaxYearRepository $taxYears, ?TaxYear $except = null): void
    {
        foreach ($taxYears->findAllOrdered() as $existing) {
            if (null !== $except && $existing->getId() === $except->getId()) {
                continue;
            }

            $existing->setActive(false);
        }
    }
}
