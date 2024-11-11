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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('dashboard');
        $rootNode = $treeBuilder->getRootNode();
        $childNodes = $rootNode->children();
        $this->appendManageConfiguration($childNodes);

        return $treeBuilder;
    }

    private function appendManageConfiguration(NodeBuilder $childNodes): void
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
            ->end()
            ->scalarNode('administrator_teams')
                ->info('All users in these teams get the administrator role. Teams is a string containing roles seperated by comma\'s')
                ->isRequired()
            ->end()
            ->scalarNode('surfconext_representative_authorization')
                ->info(
                    'All users in these teams get the ROLE_SURFCONEXT_REPRESENTATIVE role. Teams is a string ' .
                    'containing roles seperated by comma\'s'
                )
                ->isRequired()
            ->end()
            ->scalarNode('authorization_attribute_name')
                ->info(
                    'The authorization attribute name should refer to a  multivalue SAML attribute that contain the ' .
                    'urn:mace:surfnet.nl:surfnet.nl:sab:organizationCode and urn:mace:surfnet.nl:surfnet.nl:sab:role values.'
                )
                ->isRequired()
            ->end();
    }
}
