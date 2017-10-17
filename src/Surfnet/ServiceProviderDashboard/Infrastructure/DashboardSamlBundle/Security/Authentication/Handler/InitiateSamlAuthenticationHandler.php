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
use Surfnet\SamlBundle\Monolog\SamlAuthenticationLogger;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Authentication\AuthenticatedSessionStateHandler;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Authentication\SamlAuthenticationStateHandler;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Authentication\SamlInteractionProvider;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class InitiateSamlAuthenticationHandler implements AuthenticationHandler
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
     * @var AuthenticatedSessionStateHandler
     */
    private $authenticatedSession;

    /**
     * @var SamlAuthenticationStateHandler
     */
    private $samlAuthenticationStateHandler;

    /**
     * @var SamlInteractionProvider
     */
    private $samlInteractionProvider;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var SamlAuthenticationLogger
     */
    private $authenticationLogger;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        TokenStorageInterface $tokenStorageInterface,
        AuthenticatedSessionStateHandler $authenticatedSession,
        SamlAuthenticationStateHandler $samlAuthenticationStateHandler,
        SamlInteractionProvider $samlInteractionProvider,
        RouterInterface $router,
        SamlAuthenticationLogger $authenticationLogger,
        LoggerInterface $logger
    ) {
        $this->tokenStorage                   = $tokenStorageInterface;
        $this->authenticatedSession           = $authenticatedSession;
        $this->samlAuthenticationStateHandler = $samlAuthenticationStateHandler;
        $this->samlInteractionProvider        = $samlInteractionProvider;
        $this->router                         = $router;
        $this->authenticationLogger           = $authenticationLogger;
        $this->logger                         = $logger;
    }

    public function process(GetResponseEvent $event)
    {
        $acsUri = $this->router->generate('dashboard_saml_consume_assertion');

        // we have no logged in user, and have sent an authentication request, but do not receive a response POSTed
        // back to our ACS. This means that a user may have inadvertedly triggered the sending of an AuthnRequest
        // one of the common causes of this is the prefetching of pages by browsers to give users the illusion of speed.
        // In any case, we reset the login and send a new AuthnRequest.
        if ($this->tokenStorage->getToken() === null
            && $this->samlInteractionProvider->isSamlAuthenticationInitiated()
            && $event->getRequest()->getMethod() !== 'POST'
            && $event->getRequest()->getRequestUri() !== $acsUri
        ) {
            $this->logger->notice(
                'No authenticated user, a AuthnRequest was sent, but the current request is not a POST to our ACS '
                . 'thus we assume the user attempts to access the application again (possibly after a browser '
                . 'prefetch). Resetting the login state so that a new AuthnRequest can be sent.'
            );

            $this->samlInteractionProvider->reset();
        }

        if ($this->tokenStorage->getToken() === null
            && !$this->samlInteractionProvider->isSamlAuthenticationInitiated()
        ) {
            $this->logger->notice('No authenticated user, no saml AuthnRequest pending, sending new AuthnRequest');

            $this->authenticatedSession->setCurrentRequestUri($event->getRequest()->getUri());
            $event->setResponse($this->samlInteractionProvider->initiateSamlRequest());

            $logger = $this->authenticationLogger->forAuthentication(
                $this->samlAuthenticationStateHandler->getRequestId()
            );
            $logger->notice('Sending AuthnRequest');

            return;
        }

        if ($this->nextHandler) {
            $this->nextHandler->process($event);
        }
    }

    public function setNext(AuthenticationHandler $handler)
    {
        $this->nextHandler = $handler;
    }
}
