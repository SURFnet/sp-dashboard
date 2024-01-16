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

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig_Environment;
use Twig\Extension\AbstractExtension;
use Twig_SimpleFunction;

class IdentityExtension extends AbstractExtension
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function getFunctions(): array
    {
        return [
            new Twig_SimpleFunction(
                'identity',
                $this->renderIdentity(...),
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
        ];
    }

    /**
     * @return string
     */
    public function renderIdentity(Twig_Environment $environment)
    {
        $token = $this->tokenStorage->getToken();
        $contact = null;

        if ($token !== null) {
            $contact = $token->getUser()->getContact();
        }

        if (!$contact) {
            return '';
        }

        return $environment->render(
            'TwigExtension\\identity.html.twig',
            [
                'contact' => $contact,
            ]
        );
    }
}
