<?php

declare(strict_types = 1);

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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class DashboardSamlExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();

        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader(
            $container,
            new FileLocator(
                __DIR__.'/../Resources/config'
            )
        );
        $loader->load('services.yml');

        $container->setParameter(
            'surfnet.dashboard.security.authentication.administrator_teams',
            $config['administrator_teams']
        );
        $container->setParameter(
            'surfnet.dashboard.security.authentication.session.maximum_absolute_lifetime_in_seconds',
            $config['session_lifetimes']['max_absolute_lifetime']
        );
        $container->setParameter(
            'surfnet.dashboard.security.authentication.session.maximum_relative_lifetime_in_seconds',
            $config['session_lifetimes']['max_relative_lifetime']
        );
    }
}
