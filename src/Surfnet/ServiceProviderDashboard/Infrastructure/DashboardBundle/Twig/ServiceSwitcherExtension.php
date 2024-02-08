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

use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Service\ServiceSwitcherType;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\AuthorizationService;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ServiceSwitcherExtension extends AbstractExtension
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly FormFactoryInterface $formFactory,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'service_switcher',
                $this->render(...),
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
        ];
    }

    public function render(Environment $environment): string
    {
        $token = $this->tokenStorage->getToken();

        if ($token === null) {
            return '';
        }
        $roles = $token->getRoleNames();
        if (!in_array('ROLE_ADMINISTRATOR', $roles)) {
            return '';
        }

        $form = $this->formFactory->create(ServiceSwitcherType::class);

        return $environment->render(
            'TwigExtension\\service_switcher.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }
}
