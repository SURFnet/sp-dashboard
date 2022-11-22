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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Firewall;

use Exception;
use Psr\Log\LoggerInterface;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Authentication\Handler\AuthenticationHandler;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Authentication\SamlInteractionProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Firewall\AbstractListener;

class SamlListener extends AbstractListener
{
    public function __construct(
        private readonly AuthenticationHandler $authenticationHandler,
        private readonly SamlInteractionProvider $samlInteractionProvider,
        private readonly LoggerInterface $logger
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return true;
    }

    public function authenticate(RequestEvent $event)
    {
        try {
            $this->authenticationHandler->process($event);
        } catch (AuthenticationException $exception) {
            $this->samlInteractionProvider->reset();

            $this->logger->warning(sprintf(
                'Could not authenticate, AuthenticationException encountered: "%s"',
                $exception->getMessage()
            ));

            throw $exception;
        } catch (Exception $exception) {
            $this->samlInteractionProvider->reset();

            $this->logger->error(sprintf(
                'Could not authenticate, Exception of type "%s" encountered: "%s"',
                get_class($exception),
                $exception->getMessage()
            ));

            throw $exception;
        }
    }
}
