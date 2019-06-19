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
        $rootNode = $treeBuilder->root('dashboard');
        $childNodes = $rootNode->children();
        $this->appendManageConfiguration($childNodes);

        return $treeBuilder;
    }

    /**
     * @param NodeBuilder $childNodes
     */
    private function appendManageConfiguration(NodeBuilder $childNodes)
    {
        $childNodes
            ->arrayNode('manage')
                ->info('The manage configuration root')
                ->isRequired()
                ->useAttributeAsKey('environment')
                ->arrayPrototype()
                    ->children()
                        ->arrayNode('connection')
                            ->children()
                                ->scalarNode('host')->end()
                                ->scalarNode('username')->end()
                                ->scalarNode('password')->end()
                            ->end()
                        ->end()
                        ->scalarNode('publication_status')->end()
                    ->end()
                ->end()
            ->end();
    }
}
