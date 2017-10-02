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
namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Twig;

use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\AdminSwitcherService;
use Twig_Environment;
use Twig_Extension;
use Twig_SimpleFunction;

class AdminSwitcherExtension extends Twig_Extension
{
    private $service;

    public function __construct(AdminSwitcherService $service)
    {
        $this->service = $service;
    }

    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction(
                'admin_switcher',
                [$this, 'renderAdminSwitcher'],
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
        ];
    }

    public function renderAdminSwitcher(Twig_Environment $environment)
    {
        return $environment->render(
            'DashboardBundle:TwigExtension:admin_switcher.html.twig',
            [
                'suppliers' => $this->service->getSupplierOptions(),
                'selected_supplier' => $this->service->getSelectedSupplier(),
            ]
        );
    }
}
