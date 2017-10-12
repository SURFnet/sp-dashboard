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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('dashboard_saml');

        $childNodes = $rootNode->children();

        $this->appendSessionConfiguration($childNodes);

        return $treeBuilder;
    }

    /**
     * @param NodeBuilder $childNodes
     */
    private function appendSessionConfiguration(NodeBuilder $childNodes)
    {
        $childNodes
            ->scalarNode('administrator_team')
                ->isRequired()
                ->defaultValue('urn:collab:org:surf.nl')
                ->info('All users in this team get the administrator role')
            ->end()
            ->arrayNode('session_lifetimes')
                ->isRequired()
                ->children()
                    ->integerNode('max_absolute_lifetime')
                        ->isRequired()
                        ->defaultValue(3600)
                        ->info('The maximum lifetime of a session regardless of interaction by the user, in seconds.')
                        ->example('3600 -> 1 hour * 60 minutes * 60 seconds')
                        ->validate()
                            ->ifTrue(
                                function ($lifetime) {
                                    return !is_int($lifetime);
                                }
                            )
                            ->thenInvalid('max_absolute_lifetime must be an integer')
                        ->end()
                    ->end()
                    ->integerNode('max_relative_lifetime')
                        ->isRequired()
                        ->defaultValue(600)
                        ->info(
                            'The maximum relative lifetime of a session; the maximum allowed time between two '
                            . 'interactions by the user'
                        )
                        ->example('600 -> 10 minutes * 60 seconds')
                        ->validate()
                            ->ifTrue(
                                function ($lifetime) {
                                    return !is_int($lifetime);
                                }
                            )
                            ->thenInvalid('max_relative_lifetime must be an integer')
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
