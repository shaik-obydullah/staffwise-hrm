<?php

namespace App\Controller;

use App\Entity\AdminUser;
use App\Repository\AdminUserRepository;
use App\Service\AuditLogger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils, AdminUserRepository $adminUsers): Response
    {
        if (null === $adminUsers->findAdmin() && null === $authenticationUtils->getLastAuthenticationError()) {
            return $this->redirectToRoute('app_setup');
        }

        return $this->render('security/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route('/logout', name: 'app_logout', methods: ['GET'])]
    public function logout(): never
    {
        throw new \LogicException('This method is intercepted by the logout key on the firewall.');
    }

    #[Route('/lock', name: 'app_lock', methods: ['GET'])]
    public function lock(): Response
    {
        return $this->redirectToRoute('app_logout');
    }

    #[Route('/setup', name: 'app_setup', methods: ['GET', 'POST'])]
    public function setup(
        Request $request,
        AdminUserRepository $adminUsers,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        AuditLogger $auditLogger,
    ): Response {
        if (null !== $adminUsers->findAdmin()) {
            return $this->redirectToRoute('app_login');
        }

        $error = null;

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('setup_admin', (string) $request->request->get('_token'))) {
                $error = 'Your session expired. Please try again.';
            } else {
                $pin = (string) $request->request->get('pin', '');
                $confirm = (string) $request->request->get('confirm_pin', '');

                if (!preg_match('/^\d{4,8}$/', $pin)) {
                    $error = 'Choose a PIN between 4 and 8 digits.';
                } elseif ($pin !== $confirm) {
                    $error = 'The PIN confirmation does not match.';
                } else {
                    $admin = (new AdminUser())->setUsername('admin');
                    $admin->setPinHash($passwordHasher->hashPassword($admin, $pin));

                    $entityManager->persist($admin);
                    $entityManager->flush();

                    $auditLogger->log('admin.created', 'AdminUser', $admin->getId(), 'Administrator PIN created.');

                    $this->addFlash('success', 'Administrator PIN created. Sign in to continue.');

                    return $this->redirectToRoute('app_login');
                }
            }
        }

        return $this->render('security/setup.html.twig', ['error' => $error]);
    }
}
