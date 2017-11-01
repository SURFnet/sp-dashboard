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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Application\CommandHandler\Entity;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\CreateEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\Entity\CreateEntityCommandHandler;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Repository\EntityRepository;

class CreateEntityCommandHandlerTest extends MockeryTestCase
{
    /** @var CreateEntityCommandHandler */
    private $commandHandler;

    /** @var ServiceRepository|m\MockInterface */
    private $repository;

    public function setUp()
    {
        $this->repository = m::mock(EntityRepository::class);
        $this->commandHandler = new CreateEntityCommandHandler($this->repository);
    }

    /**
     * @group CommandHandler
     */
    public function test_it_should_handle_a_create_service_command()
    {
        $command = new CreateEntityCommand(
            'd3d21618-b643-4b73-a971-bf735dd46481',
            m::mock(Service::class),
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
     * @expectedExceptionMessage The id that was generated for the entity was not unique, please try again
     */
    public function test_it_should_reject_an_existing_id_collision()
    {
        $command = new CreateEntityCommand(
            'd3d21618-b643-4b73-a971-bf735dd46481',
            m::mock(Service::class),
            '3500b012-b1ab-48d6-b3b1-2932a3db4c79'
        );

        $this->repository->shouldReceive('isUnique')->with('d3d21618-b643-4b73-a971-bf735dd46481')->andReturn(false);

        $this->commandHandler->handle($command);
    }
}
