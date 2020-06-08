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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Infrastructure\DashboardBundle\CommandHandler\Service;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Command\Service\SelectServiceCommand;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\CommandHandler\Service\SelectServiceCommandHandler;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\AuthorizationService;

class SelectServiceCommandHandlerTest extends MockeryTestCase
{
    /** @var SelectServiceCommandHandler */
    private $commandHandler;

    /** @var AuthorizationService|m\MockInterface */
    private $authService;

    public function setUp()
    {
        $this->authService = m::mock(AuthorizationService::class);
        $this->commandHandler = new SelectServiceCommandHandler($this->authService);
    }

    /**
     * @group CommandHandler
     */
    public function test_handler_processes_command_and_selects_service()
    {
        $command = new SelectServiceCommand();
        $command->setSelectedServiceId('ibuildings');

        $this->authService->shouldReceive('changeActiveService')->with('ibuildings')->once();

        $this->commandHandler->handle($command);
    }
}
