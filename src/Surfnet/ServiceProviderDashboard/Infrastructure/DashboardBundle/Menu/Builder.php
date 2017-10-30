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
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\AuthorizationService;

class Builder
{
    public function __construct(FactoryInterface $factory, AuthorizationService $authorizationService)
    {
        $this->factory = $factory;
        $this->authorizationService = $authorizationService;
    }

    public function mainMenu(array $options)
    {
        $menu = $this->factory->createItem('root');

        if (!$this->authorizationService->isLoggedIn()) {
            return $menu;
        }

        if ($this->authorizationService->hasActiveServiceId()) {
            $menu->addChild('My entities', array('route' => 'entity_list'));

            $menu->addChild('Add new entity', array(
                'route' => 'entity_add',
            ));

            $menu->addChild('Privacy', array(
                'route' => 'privacy_questions',
            ));
        }

        if ($this->authorizationService->isAdministrator()) {
            $menu->addChild('Add new service', array(
                'route' => 'service_add',
            ));

            if ($this->authorizationService->hasActiveServiceId()) {
                $menu->addChild('Edit service', array(
                    'route' => 'service_edit',
                ));
            }

            $menu->addChild('Translations', array(
                'route' => 'lexik_translation_overview',
            ));
        }

        return $menu;
    }
}
