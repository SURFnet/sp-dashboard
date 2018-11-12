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
namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Twig;

use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\AuthorizationService;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig_Environment;
use Twig_Extension;
use Twig_SimpleFunction;

class ServiceSwitcherExtension extends Twig_Extension
{
    /**
     * @var AuthorizationService
     */
    private $authorizationService;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage, AuthorizationService $authorizationService)
    {
        $this->tokenStorage = $tokenStorage;
        $this->authorizationService = $authorizationService;
    }

    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction(
                'service_switcher',
                [$this, 'render'],
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
        ];
    }

    /**
     * @return string
     */
    public function render(Twig_Environment $environment)
    {
        $token = $this->tokenStorage->getToken();
        if (!$token || !$token->hasRole('ROLE_ADMINISTRATOR')) {
            return '';
        }

        $allowedServices = $this->authorizationService->getAllowedServiceNamesById();
        if (count($allowedServices) <= 1) {
            return '';
        }

        return $environment->render(
            'DashboardBundle:TwigExtension:service_switcher.html.twig',
            [
                'services' => $allowedServices,
                'selected_service' => $this->authorizationService->getActiveServiceId(),
            ]
        );
    }
}
