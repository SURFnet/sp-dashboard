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
use SAML2\Assertion;
use SAML2\Response\Exception\PreconditionNotMetException;
use Surfnet\SamlBundle\Http\Exception\AuthnFailedSamlResponseException;
use Surfnet\SamlBundle\Monolog\SamlAuthenticationLogger;
use Surfnet\SamlBundle\SAML2\Response\Assertion\InResponseTo;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Authentication\AuthenticatedSessionStateHandler;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Authentication\SamlAuthenticationStateHandler;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Authentication\SamlInteractionProvider;
use Twig\Environment;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class ProcessSamlAuthenticationHandler implements AuthenticationHandler
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly SamlInteractionProvider $samlInteractionProvider,
        private readonly SamlAuthenticationStateHandler $authenticationStateHandler,
        private readonly AuthenticatedSessionStateHandler $authenticatedSession,
        private readonly SamlAuthenticationLogger $authenticationLogger,
        private readonly Environment $templating
    ) {
    }

    public function process(Request $request): Assertion
    {
        $expectedInResponseTo = $this->authenticationStateHandler->getRequestId();
        $logger = $this->authenticationLogger->forAuthentication($expectedInResponseTo);

        $logger->notice('No authenticated user and AuthnRequest pending, attempting to process SamlResponse');

        try {
            $assertion = $this->samlInteractionProvider->processSamlResponse($request);
        } catch (AuthnFailedSamlResponseException $exception) {
            $logger->notice(sprintf('SAML Authentication failed at IdP: "%s"', $exception->getMessage()));
            $responseBody = $this->templating->render(
                'DashboardSamlBundle/Exception/authnFailed.html.twig',
                ['exception' => $exception, 'page_title' => 'AuthnFailedSamlResponseException']
            );

            return new Response($responseBody, Response::HTTP_UNAUTHORIZED);
        } catch (PreconditionNotMetException $exception) {
            $logger->notice(sprintf('SAMLResponse precondition not met: "%s"', $exception->getMessage()));
            $responseBody = $this->templating->render(
                'DashboardSamlBundle/Exception/preconditionNotMet.html.twig',
                ['exception' => $exception, 'page_title' => 'PreconditionNotMetException']
            );

            return new Response($responseBody, Response::HTTP_UNAUTHORIZED);
        } catch (Exception $exception) {
            $logger->error(sprintf('Failed SAMLResponse Parsing: "%s"', $exception->getMessage()));

            throw new AuthenticationException('Failed SAMLResponse parsing', 0, $exception);
        }

        if (!InResponseTo::assertEquals($assertion, $expectedInResponseTo)) {
            $logger->error('Unknown or unexpected InResponseTo in SAMLResponse');

            throw new AuthenticationException('Unknown or unexpected InResponseTo in SAMLResponse');
        }
        return $assertion;
    }
}
