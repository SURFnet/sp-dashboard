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

use Exception;
use SAML2_Response_Exception_PreconditionNotMetException as PreconditionNotMetException;
use Surfnet\SamlBundle\Http\Exception\AuthnFailedSamlResponseException;
use Surfnet\SamlBundle\Monolog\SamlAuthenticationLogger;
use Surfnet\SamlBundle\SAML2\Response\Assertion\InResponseTo;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Authentication\AuthenticatedSessionStateHandler;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Authentication\SamlAuthenticationStateHandler;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Authentication\SamlInteractionProvider;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Authentication\Token\SamlToken;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Exception\UnknownServiceException;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) SamlResponse parsing, validation authentication and error handling
 *                                                 requires quite a few classes as it is fairly complex.
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
class ProcessSamlAuthenticationHandler implements AuthenticationHandler
{
    /**
     * @var AuthenticationHandler
     */
    private $nextHandler;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var SamlInteractionProvider
     */
    private $samlInteractionProvider;

    /**
     * @var SamlAuthenticationStateHandler
     */
    private $authenticationStateHandler;

    /**
     * @var AuthenticatedSessionStateHandler
     */
    private $authenticatedSession;

    /**
     * @var AuthenticationManagerInterface
     */
    private $authenticationManager;

    /**
     * @var SamlAuthenticationLogger
     */
    private $authenticationLogger;

    /**
     * @var EngineInterface
     */
    private $templating;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        SamlInteractionProvider $samlInteractionProvider,
        SamlAuthenticationStateHandler $authenticationStateHandler,
        AuthenticatedSessionStateHandler $authenticatedSession,
        AuthenticationManagerInterface $authenticationManager,
        SamlAuthenticationLogger $authenticationLogger,
        EngineInterface $templating
    ) {
        $this->tokenStorage               = $tokenStorage;
        $this->samlInteractionProvider    = $samlInteractionProvider;
        $this->authenticationStateHandler = $authenticationStateHandler;
        $this->authenticatedSession       = $authenticatedSession;
        $this->authenticationManager      = $authenticationManager;
        $this->authenticationLogger       = $authenticationLogger;
        $this->templating                 = $templating;
    }

    public function process(GetResponseEvent $event)
    {
        if ($this->tokenStorage->getToken() === null
            && $this->samlInteractionProvider->isSamlAuthenticationInitiated()
        ) {
            $expectedInResponseTo = $this->authenticationStateHandler->getRequestId();
            $logger               = $this->authenticationLogger->forAuthentication($expectedInResponseTo);

            $logger->notice('No authenticated user and AuthnRequest pending, attempting to process SamlResponse');

            try {
                $assertion = $this->samlInteractionProvider->processSamlResponse($event->getRequest());
            } catch (AuthnFailedSamlResponseException $exception) {
                $logger->notice(sprintf('SAML Authentication failed at IdP: "%s"', $exception->getMessage()));
                $responseBody = $this->templating->render(
                    'DashboardSamlBundle:Exception:authnFailed.html.twig',
                    ['exception' => $exception]
                );

                $event->setResponse(new Response($responseBody, Response::HTTP_UNAUTHORIZED));

                return;
            } catch (PreconditionNotMetException $exception) {
                $logger->notice(sprintf('SAMLResponse precondition not met: "%s"', $exception->getMessage()));
                $responseBody = $this->templating->render(
                    'DashboardSamlBundle:Exception:preconditionNotMet.html.twig',
                    ['exception' => $exception]
                );

                $event->setResponse(new Response($responseBody, Response::HTTP_UNAUTHORIZED));

                return;
            } catch (Exception $exception) {
                $logger->error(sprintf('Failed SAMLResponse Parsing: "%s"', $exception->getMessage()));

                throw new AuthenticationException('Failed SAMLResponse parsing', 0, $exception);
            }

            if (!InResponseTo::assertEquals($assertion, $expectedInResponseTo)) {
                $logger->error('Unknown or unexpected InResponseTo in SAMLResponse');

                throw new AuthenticationException('Unknown or unexpected InResponseTo in SAMLResponse');
            }

            $logger->notice('Successfully processed SAMLResponse, attempting to authenticate');

            $token            = new SamlToken();
            $token->assertion = $assertion;

            try {
                $authToken = $this->authenticationManager->authenticate($token);
            } catch (AuthenticationException $failed) {
                $logger->error(sprintf('Authentication Failed, reason: "%s"', $failed->getMessage()));

                // By default deny authorization
                $event->setResponse(new Response('', Response::HTTP_FORBIDDEN));

                return;
            } catch (UnknownServiceException $exception) {
                $responseBody = $this->templating->render(
                    'DashboardSamlBundle:Exception:unknownService.html.twig',
                    ['teamNames' => $exception->getTeamNames()]
                );

                $event->setResponse(new Response($responseBody, Response::HTTP_UNAUTHORIZED));

                return;
            }

            $this->authenticatedSession->logAuthenticationMoment();
            $this->tokenStorage->setToken($authToken);

            // migrate the session to prevent session hijacking
            $this->authenticatedSession->migrate();

            $event->setResponse(new RedirectResponse($this->authenticatedSession->getCurrentRequestUri()));

            $logger->notice('Authentication succeeded, redirecting to original location');

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
