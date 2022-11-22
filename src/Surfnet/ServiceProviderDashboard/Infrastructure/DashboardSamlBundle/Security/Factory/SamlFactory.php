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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Factory;

use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Authentication\Provider\SamlProvider;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Firewall\SamlListener;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AuthenticatorFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SamlFactory implements AuthenticatorFactoryInterface
{
    public function createAuthenticator(
        ContainerBuilder $container,
        string $firewallName,
        array $config,
        string $userProviderId
    ): array {
//        $providerId = 'security.authentication.provider.saml.' . $firewallName;
//        $container->setDefinition(
//            $providerId,
//            new ChildDefinition(SamlProvider::class)
//        );
//
//        $listenerId = 'security.authentication.listener.saml.' . $firewallName;
//        $container->setDefinition(
//            $listenerId,
//            new ChildDefinition(SamlListener::class)
//        );
//
//        $cookieHandlerId = 'security.logout.handler.cookie_clearing.' . $firewallName;
//        $cookieHandler = $container->setDefinition(
//            $cookieHandlerId,
//            new ChildDefinition('security.logout.handler.cookie_clearing')
//        );
//        $cookieHandler->addArgument([]);
//
//       return array($providerId, $listenerId);
        return [];
    }

    public function getPosition()
    {
        return 'pre_auth';
    }

    public function getKey()
    {
        return 'saml';
    }

    public function addConfiguration(NodeDefinition $builder)
    {
    }

    public function getPriority(): int
    {
        return 0;
    }
}
