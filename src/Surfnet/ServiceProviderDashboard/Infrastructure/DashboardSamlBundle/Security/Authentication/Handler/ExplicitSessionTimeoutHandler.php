<?php

/**
 * Copyright 2017 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Authentication\Handler;

use Psr\Log\LoggerInterface;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Authentication\AuthenticatedSessionStateHandler;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Authentication\Session\SessionLifetimeGuard;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Logout\CookieClearingLogoutHandler;
use Symfony\Component\Security\Http\Logout\SessionLogoutHandler;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ExplicitSessionTimeoutHandler implements AuthenticationHandler
{
    /**
     * @var AuthenticationHandler|null
     */
    private $nextHandler;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var SessionLifetimeGuard
     */
    private $sessionLifetimeGuard;

    /**
     * @var AuthenticatedSessionStateHandler
     */
    private $authenticatedSession;

    /**
     * @var SessionLogoutHandler
     */
    private $sessionLogoutHandler;

    /**
     * @var CookieClearingLogoutHandler
     */
    private $cookieClearingLogoutHandler;

    /**
     * @var RouterInterface
     */
    private $router;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        TokenStorageInterface $tokenStorageInterface,
        AuthenticatedSessionStateHandler $authenticatedSessionStateHandler,
        SessionLifetimeGuard $sessionLifetimeGuard,
        SessionLogoutHandler $sessionLogoutHandler,
        CookieClearingLogoutHandler $cookieClearingLogoutHandler,
        RouterInterface $router,
        LoggerInterface $logger
    ) {
        $this->tokenStorage                = $tokenStorageInterface;
        $this->authenticatedSession        = $authenticatedSessionStateHandler;
        $this->sessionLifetimeGuard        = $sessionLifetimeGuard;
        $this->sessionLogoutHandler        = $sessionLogoutHandler;
        $this->cookieClearingLogoutHandler = $cookieClearingLogoutHandler;
        $this->router                      = $router;
        $this->logger                      = $logger;
    }

    public function process(GetResponseEvent $event)
    {
        if ($this->tokenStorage->getToken() !== null
            && !$this->sessionLifetimeGuard->sessionLifetimeWithinLimits($this->authenticatedSession)
        ) {
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
                implode(' and ', $invalidatedBy)
            ));


            $token   = $this->tokenStorage->getToken();
            $request = $event->getRequest();

            // if the current request was not a GET request we cannot safely redirect to that page after login as it
            // may require a form resubmit for instance. Therefor, we redirect to the last GET request (either current
            // or previous).
            $afterLoginRedirectTo = $this->authenticatedSession->getCurrentRequestUri();
            if ($event->getRequest()->getMethod() === 'GET') {
                $afterLoginRedirectTo = $event->getRequest()->getRequestUri();
            }

            // log the user out using Symfony methodology, see the LogoutListener
            $event->setResponse(new RedirectResponse($this->router->generate('service_list')));

            $this->sessionLogoutHandler->logout($request, $event->getResponse(), $token);
            $this->cookieClearingLogoutHandler->logout($request, $event->getResponse(), $token);
            $this->tokenStorage->setToken(null);

            // the session is restarted after invalidation during the logout, so we can (re)store the last GET request
            $this->authenticatedSession->setCurrentRequestUri($afterLoginRedirectTo);

            return;
        }

        if ($this->nextHandler !== null) {
            $this->nextHandler->process($event);
        }
    }

    public function setNext(AuthenticationHandler $handler)
    {
        $this->nextHandler = $handler;
    }
}
