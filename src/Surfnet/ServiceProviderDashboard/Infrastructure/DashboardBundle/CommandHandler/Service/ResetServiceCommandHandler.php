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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\CommandHandler\Service;

use Surfnet\ServiceProviderDashboard\Application\CommandHandler\CommandHandler;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Command\Service\ResetServiceCommand;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Command\Service\SelectServiceCommand;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\AuthorizationService;

class ResetServiceCommandHandler implements CommandHandler
{
    /**
     * @var AuthorizationService
     */
    private $service;

    public function __construct(AuthorizationService $service)
    {
        $this->service = $service;
    }

    /**
     * @param ResetServiceCommand $command
     */
    public function handle(ResetServiceCommand $command)
    {
        $this->service->resetService();
    }
}
