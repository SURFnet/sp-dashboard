<?php

//declare(strict_types = 1);

/**
 * Copyright 2023 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Security\Authentication\Handler;

use Psr\Log\LoggerInterface;
use Surfnet\SamlBundle\Security\Authentication\Handler\FailureHandler as SamlBundleFailureHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\HttpUtils;
use Twig\Environment;

class FailureHandler extends SamlBundleFailureHandler
{
    public function __construct(
        HttpKernelInterface          $httpKernel,
        HttpUtils                    $httpUtils,
        private readonly Environment $templating,
        array                        $options = [],
        LoggerInterface              $logger = null,
    ) {
        parent::__construct($httpKernel, $httpUtils, $options, $logger);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $this->logger->error(sprintf('Authentication Failed, reason: "%s"', $exception->getMessage()));
        $responseBody = $this->templating->render('@Twig/Exception/authnFailed.html.twig', ['exception' => $exception]);
        return new Response($responseBody, Response::HTTP_UNAUTHORIZED);
    }
}
