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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\DependencyInjection;

use Exception;
use Surfnet\ServiceProviderDashboard\Application\ViewObject\Apis\ApiConfig as Config;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class DashboardExtension extends Extension
{
    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();

        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(
            __DIR__.'/../Resources/config'
        ));
        $loader->load('services.yml');

        $serviceContainer = $container->get('service_container');
        $environment = $serviceContainer->getParameter('kernel.environment');
        $rootDir = $serviceContainer->getParameter('kernel.root_dir');

        if ($environment === 'test') {
            $loader->load($rootDir.'/../tests/webtests/Resources/config/services.yml');
        }

        foreach ($config['manage'] as $environment => $manageConfig) {
            $this->parseManageConfiguration($environment, $manageConfig, $container);
        }

        $this->parseTeamsConfiguration($config['teams'], $container);
    }

    /**
     * Creates a manage config aggregate based on the configuration in config.yml.
     *
     * Each environment will get a separate service named:
     *
     * surfnet.manage.configuration.%env_name
     *
     * @param $environment
     * @param $config
     * @param $container
     */
    public function parseManageConfiguration($environment, $config, $container)
    {
        $manageConfiguration = new Definition(Config::class);
        $manageConfiguration->setClass(Config::class);
        $manageConfiguration
            ->setFactory('Surfnet\ServiceProviderDashboard\Application\ViewObject\Apis\ApiConfigFactory::fromConfig');
        $manageConfiguration->setArguments([$config, $environment]);
        $container->setDefinition('surfnet.manage.configuration.' . $environment, $manageConfiguration);
    }

    /**
     * Creates a config aggregate based on the configuration in config.yml for teams.
     */
    public function parseTeamsConfiguration(array $config, ContainerBuilder $container)
    {
        $configuration = new Definition(Config::class);
        $configuration->setClass(Config::class);
        $configuration->setFactory(
            'Surfnet\ServiceProviderDashboard\Application\ViewObject\Apis\ApiConfigFactory::fromConfig'
        );
        $container->setDefinition('surfnet.teams.configuration', $configuration);
        $configuration->setArguments([$config]);
    }
}
