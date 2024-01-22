<?php

declare(strict_types = 1);

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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\EventListener;

use Psr\Log\LoggerInterface;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Exception\UnknownServiceException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Twig\Environment;

class ExceptionListener
{
    public function __construct(
        private readonly Environment $templating,
    ) {
    }

    public function __invoke(ExceptionEvent $event): void
    {
        // You get the exception object from the received event
        $exception = $event->getThrowable();
        if ($exception instanceof UnknownServiceException) {
            $responseBody = $this->templating->render(
                '@Dashboard/bundles/TwigBundle/Exception/unknownService.html.twig',
                ['teamNames' => $exception->getTeamNames()]
            );

            $event->setResponse(new Response($responseBody, Response::HTTP_UNAUTHORIZED));
        }
    }
}
