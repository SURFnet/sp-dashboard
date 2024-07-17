<?php

declare(strict_types = 1);

/**
 * Copyright 2024 SURFnet bv
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\EventListener;

use Psr\Log\LoggerInterface;
use Surfnet\StepupSelfService\SelfServiceBundle\Security\Authentication\AuthenticatedSessionStateHandler;
use Surfnet\StepupSelfService\SelfServiceBundle\Security\Authentication\Session\SessionLifetimeGuard;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final readonly class ExplicitSessionTimeoutListener implements EventSubscriberInterface
{
    public function __construct(
        private TokenStorageInterface            $tokenStorage,
        private AuthenticatedSessionStateHandler $authenticatedSession,
        #[Autowire(service: 'self_service.security.authentication.session.session_lifetime_guard')]
        private SessionLifetimeGuard             $sessionLifetimeGuard,
        private RouterInterface                  $router,
        private LoggerInterface                  $logger,
        private EventDispatcherInterface         $eventDispatcher,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // The firewall, which makes the token available, listens at P8
            // We must jump in after the firewall, forcing us to overwrite the translator locale.
            KernelEvents::REQUEST => ['checkSessionTimeout', 5],
        ];
    }

    public function checkSessionTimeout(RequestEvent $event): void
    {
        $token = $this->tokenStorage->getToken();

        if ($token === null || $this->sessionLifetimeGuard->sessionLifetimeWithinLimits($this->authenticatedSession)) {
            return;
        }

        $invalidatedBy = [];
        if (!$this->sessionLifetimeGuard->sessionLifetimeWithinAbsoluteLimit($this->authenticatedSession)) {
            $invalidatedBy[] = 'absolute';
        }

        if (!$this->sessionLifetimeGuard->sessionLifetimeWithinRelativeLimit($this->authenticatedSession)) {
            $invalidatedBy[] = 'relative';
        }

        $this->logger->notice(sprintf(
            'Authenticated user found, but session was determined to be outside of the "%s" time limit. User will '
            . 'be logged out and redirected to session-expired page to attempt new login.',
            implode(' and ', $invalidatedBy),
        ));

        $request = $event->getRequest();

        // if the current request was not a GET request we cannot safely redirect to that page after login as it
        // may require a form resubmit for instance. Therefor, we redirect to the last GET request (either current
        // or previous).
        $afterLoginRedirectTo = $this->authenticatedSession->getCurrentRequestUri();

        if ($event->getRequest()->getMethod() === 'GET') {
            $afterLoginRedirectTo = $event->getRequest()->getRequestUri();
        }

        // log the user out using Symfony methodology, see the LogoutListener
        $event->setResponse(new RedirectResponse($this->router->generate('selfservice_security_session_expired')));

        // something to clear cookies
        $this->eventDispatcher->dispatch(new LogoutEvent($request, $token));
        $this->tokenStorage->setToken(null);

        // the session is restarted after invalidation during the logout, so we can (re)store the last GET request
        $this->authenticatedSession->setCurrentRequestUri($afterLoginRedirectTo);
    }
}
