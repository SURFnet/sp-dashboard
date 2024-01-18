<?php

//declare(strict_types = 1);

/**
 * Copyright 2019 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\DependencyInjection\Compiler;

use Surfnet\ServiceProviderDashboard\Application\Metadata\JsonGeneratorStrategy;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class JsonGeneratorStrategyCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $contextDefinition = $container->findDefinition(JsonGeneratorStrategy::class);
        // Scrape the service definitions tagged with dashboard.json_generator
        $strategyServices = $container->findTaggedServiceIds('dashboard.json_generator');

        $services = [];
        foreach ($strategyServices as $identifier => $protocols) {
            // Some services are tagged multiple times, as they support multiple protocols.
            foreach ($protocols as $protocol) {
                $services[$protocol['identifier']] = new Reference($identifier);
            }
        }

        // Register the JsonGenerator services on the strategy
        foreach ($services as $protocol => $reference) {
            $contextDefinition->addMethodCall(
                'addStrategy',
                [$protocol, $reference]
            );
        }
    }
}
