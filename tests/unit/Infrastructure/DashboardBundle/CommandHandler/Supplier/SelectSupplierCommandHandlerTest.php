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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Infrastructure\DashboardBundle\CommandHandler\Supplier;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Command\Supplier\SelectSupplierCommand;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\CommandHandler\Supplier\SelectSupplierCommandHandler;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\AdminSwitcherService;

class SelectSupplierCommandHandlerTest extends MockeryTestCase
{
    /** @var SelectSupplierCommandHandler */
    private $commandHandler;

    /** @var AdminSwitcherService|m\MockInterface */
    private $service;

    public function setUp()
    {
        $this->service = m::mock(AdminSwitcherService::class);
        $this->commandHandler = new SelectSupplierCommandHandler($this->service);
    }

    /**
     * @test
     * @group CommandHandler
     */
    public function handler_processes_command_and_selects_supplier()
    {
        $command = new SelectSupplierCommand('ibuildings');

        $this->service->shouldReceive('setSelectedSupplier')->with('ibuildings')->once();

        $this->commandHandler->handle($command);
    }
}
