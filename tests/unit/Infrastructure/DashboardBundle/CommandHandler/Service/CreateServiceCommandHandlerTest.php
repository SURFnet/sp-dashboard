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
use Surfnet\ServiceProviderDashboard\Application\Command\Service\CreateServiceCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\Service\CreateServiceCommandHandler;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Supplier;
use Surfnet\ServiceProviderDashboard\Domain\Repository\ServiceRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Command\Supplier\SelectSupplierCommand;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\CommandHandler\Supplier\SelectSupplierCommandHandler;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\AuthorizationService;

class CreateServiceCommandHandlerTest extends MockeryTestCase
{
    /** @var CreateServiceCommandHandler */
    private $commandHandler;

    /** @var ServiceRepository|m\MockInterface */
    private $repository;

    public function setUp()
    {
        $this->repository = m::mock(ServiceRepository::class);
        $this->commandHandler = new CreateServiceCommandHandler($this->repository);
    }

    /**
     * @group CommandHandler
     */
    public function test_it_should_handle_a_create_service_command()
    {
        $command = new CreateServiceCommand(
            'd3d21618-b643-4b73-a971-bf735dd46481',
            m::mock(Supplier::class),
            '3500b012-b1ab-48d6-b3b1-2932a3db4c79'
        );

        $this->repository->shouldReceive('isUnique')->with('d3d21618-b643-4b73-a971-bf735dd46481')->andReturn(true);
        $this->repository->shouldReceive('save');

        $this->commandHandler->handle($command);
    }

    /**
     * @group CommandHandler
     *
     * @expectedException \Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException
     * @expectedExceptionMessage The id that was generated for the Service was not unique, please try again
     */
    public function test_it_should_reject_an_existing_id_collision()
    {
        $command = new CreateServiceCommand(
            'd3d21618-b643-4b73-a971-bf735dd46481',
            m::mock(Supplier::class),
            '3500b012-b1ab-48d6-b3b1-2932a3db4c79'
        );

        $this->repository->shouldReceive('isUnique')->with('d3d21618-b643-4b73-a971-bf735dd46481')->andReturn(false);

        $this->commandHandler->handle($command);
    }
}
