<?php

namespace App\EventSubscriber;

use App\Entity\AdminUser;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AutoLockSubscriber implements EventSubscriberInterface
{
    public const IDLE_TIMEOUT_SECONDS = 900;

    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => ['onKernelRequest', 8]];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        if (!$request->hasSession()) {
            return;
        }

        $token = $this->tokenStorage->getToken();

        if (null === $token || !$token->getUser() instanceof AdminUser) {
            return;
        }

        $session = $request->getSession();
        $lastActivity = (int) $session->get('_last_activity', 0);
        $now = time();

        if ($lastActivity > 0 && ($now - $lastActivity) > self::IDLE_TIMEOUT_SECONDS) {
            $this->tokenStorage->setToken(null);
            $session->invalidate();

            $event->setResponse(new RedirectResponse($this->urlGenerator->generate('app_login', ['locked' => 1])));

            return;
        }

        $session->set('_last_activity', $now);
    }
}
