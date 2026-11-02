<?php

namespace App\Controller;

use App\EventSubscriber\AutoLockSubscriber;
use App\Repository\AdminUserRepository;
use App\Repository\AuditLogRepository;
use App\Service\AuditLogger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/settings')]
class SettingsController extends AbstractController
{
    #[Route('', name: 'app_settings', methods: ['GET'])]
    public function index(AdminUserRepository $adminUsers, AuditLogRepository $auditLogs): Response
    {
        return $this->render('settings/index.html.twig', [
            'admin' => $adminUsers->findAdmin(),
            'auditLogs' => $auditLogs->findRecent(10),
            'idleTimeoutMinutes' => (int) (AutoLockSubscriber::IDLE_TIMEOUT_SECONDS / 60),
        ]);
    }

    #[Route('/pin', name: 'app_settings_pin', methods: ['POST'])]
    public function changePin(
        Request $request,
        AdminUserRepository $adminUsers,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        AuditLogger $audit,
    ): Response {
        if (!$this->isCsrfTokenValid('change_pin', (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $admin = $adminUsers->findAdmin();

        if (null === $admin) {
            return $this->redirectToRoute('app_setup');
        }

        $current = (string) $request->request->get('current_pin', '');
        $new = (string) $request->request->get('new_pin', '');
        $confirm = (string) $request->request->get('confirm_pin', '');

        if (!$passwordHasher->isPasswordValid($admin, $current)) {
            $this->addFlash('error', 'The current PIN is not correct.');

            return $this->redirectToRoute('app_settings');
        }

        if (!preg_match('/^\d{4,8}$/', $new)) {
            $this->addFlash('error', 'Choose a new PIN between 4 and 8 digits.');

            return $this->redirectToRoute('app_settings');
        }

        if ($new !== $confirm) {
            $this->addFlash('error', 'The new PIN confirmation does not match.');

            return $this->redirectToRoute('app_settings');
        }

        $admin->setPinHash($passwordHasher->hashPassword($admin, $new));
        $entityManager->flush();

        $audit->log('admin.pin_changed', 'AdminUser', $admin->getId(), 'Administrator PIN changed.');

        $this->addFlash('success', 'Administrator PIN updated.');

        return $this->redirectToRoute('app_settings');
    }

    #[Route('/audit', name: 'app_audit', methods: ['GET'])]
    public function audit(AuditLogRepository $auditLogs): Response
    {
        return $this->render('settings/audit.html.twig', [
            'auditLogs' => $auditLogs->findRecent(500),
        ]);
    }
}
