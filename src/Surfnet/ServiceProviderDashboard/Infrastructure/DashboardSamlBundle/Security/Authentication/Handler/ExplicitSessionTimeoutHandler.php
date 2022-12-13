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
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\EventListener\CookieClearingLogoutListener;
use Symfony\Component\Security\Http\EventListener\SessionLogoutListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ExplicitSessionTimeoutHandler implements AuthenticationHandler
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private AuthenticatedSessionStateHandler $authenticatedSessionStateHandler,
        private SessionLifetimeGuard $sessionLifetimeGuard,
        private SessionLogoutListener $sessionLogoutHandler,
        private CookieClearingLogoutListener $cookieClearingLogoutHandler,
        private RouterInterface $router,
        private LoggerInterface $logger
    ) {
    }

    public function process(Request $request): Response
    {
        if ($this->tokenStorage->getToken() !== null
            && !$this->sessionLifetimeGuard->sessionLifetimeWithinLimits($this->authenticatedSessionStateHandler)
        ) {
            $invalidatedBy = [];
            if (!$this->sessionLifetimeGuard->sessionLifetimeWithinAbsoluteLimit($this->authenticatedSessionStateHandler)) {
                $invalidatedBy[] = 'absolute';
            }

            if (!$this->sessionLifetimeGuard->sessionLifetimeWithinRelativeLimit($this->authenticatedSessionStateHandler)) {
                $invalidatedBy[] = 'relative';
            }

            $this->logger->notice(sprintf(
                'Authenticated user found, but session was determined to be outside of the "%s" time limit. User will '
                . 'be logged out and redirected to session-expired page to attempt new login.',
                implode(' and ', $invalidatedBy)
            ));


            $token = $this->tokenStorage->getToken();

            // if the current request was not a GET request we cannot safely redirect to that page after login as it
            // may require a form resubmit for instance. Therefor, we redirect to the last GET request (either current
            // or previous).
            $afterLoginRedirectTo = $this->authenticatedSessionStateHandler->getCurrentRequestUri();
            if ($request->getMethod() === 'GET') {
                $afterLoginRedirectTo = $request->getRequestUri();
            }

            // log the user out using Symfony methodology, see the LogoutListener
            $response = new RedirectResponse($this->router->generate('service_overview'));

            //$this->sessionLogoutHandler->logout($request, $event->getResponse(), $token);
            //$this->sessionLogoutHandler->onLogout($resonse);
            //$this->cookieClearingLogoutHandler->onLogout($request);
            $this->tokenStorage->setToken(null);

            // the session is restarted after invalidation during the logout, so we can (re)store the last GET request
            $this->authenticatedSessionStateHandler->setCurrentRequestUri($afterLoginRedirectTo);

            return $response;
        }
    }
}
