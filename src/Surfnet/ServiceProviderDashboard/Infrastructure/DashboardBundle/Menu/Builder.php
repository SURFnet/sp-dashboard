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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\AuthorizationService;

class Builder
{
    /**
     * @var FactoryInterface
     */
    public $factory;

    /**
     * @var AuthorizationService
     */
    public $authorizationService;

    public function __construct(FactoryInterface $factory, AuthorizationService $authorizationService)
    {
        $this->factory = $factory;
        $this->authorizationService = $authorizationService;
    }

    /**
     * @SuppressWarnings(PHPMD.ElseExpression) Using else in this situation is preferable over another less expressive
     *                                         solution
     * @return                                 ItemInterface
     */
    public function mainMenu(array $options)
    {
        $menu = $this->factory->createItem('root');

        if (!$this->authorizationService->isLoggedIn()) {
            return $menu;
        }

        if ($this->authorizationService->isAdministrator() && $this->authorizationService->getActiveServiceId()) {
            $menu->addChild(
                'global.menu.overview',
                [
                'route' => 'service_admin_overview',
                'routeParameters' => ['serviceId' => $this->authorizationService->getActiveServiceId()],
                ]
            );
        } elseif (!$this->authorizationService->isSurfConextRepresentative()) {
            $menu->addChild('global.menu.services', ['route' => 'service_overview']);
        }
        if ($this->authorizationService->isSurfConextRepresentative()) {
            $menu->addChild('global.menu.connections', ['route' => 'service_connections']);
        }
        if ($this->authorizationService->isAdministrator()) {
            $menu->addChild('global.menu.new-service', ['route' => 'service_add']);
            $menu->addChild('global.menu.translations', ['route' => 'lexik_translation_overview']);
        }

        return $menu;
    }
}
