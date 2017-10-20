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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class Builder
{
    public function __construct(FactoryInterface $factory, TokenStorageInterface $tokenStorage)
    {
        $this->factory = $factory;
        $this->tokenStorage = $tokenStorage;
    }

    public function mainMenu(array $options)
    {
        $token = $this->tokenStorage->getToken();
        $menu = $this->factory->createItem('root');

        if (!$token) {
            return $menu;
        }

        $menu->addChild('My entities', array('route' => 'entity_list'));

        $menu->addChild('Add new entity', array(
            'route' => 'entity_add',
        ));

        if ($token->hasRole('ROLE_ADMINISTRATOR')) {
            $menu->addChild('Add new service', array(
                'route' => 'service_add',
            ));
            $menu->addChild('Edit service', array(
                'route' => 'service_edit',
            ));

            $menu->addChild('Translations', array(
                'route' => 'lexik_translation_overview',
            ));
        }

        return $menu;
    }
}
