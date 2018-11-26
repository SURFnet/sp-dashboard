<?php

/**
 * Copyright 2018 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Tests\Integration\Application\CommandHandler\Service;

use Doctrine\ORM\EntityNotFoundException;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Psr\Log\LoggerInterface;
use Surfnet\ServiceProviderDashboard\Application\Command\Service\DeleteServiceCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\Service\DeleteServiceCommandHandler;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\Service\EditServiceCommandHandler;
use Surfnet\ServiceProviderDashboard\Application\Command\Service\EditServiceCommand;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Application\Service\EntityServiceInterface;
use Surfnet\ServiceProviderDashboard\Application\ViewObject\Entity;
use Surfnet\ServiceProviderDashboard\Application\ViewObject\EntityList;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Repository\ServiceRepository;

class DeleteServiceCommandHandlerTest extends MockeryTestCase
{
    /** @var DeleteServiceCommandHandler */
    private $commandHandler;

    /** @var ServiceRepository|MockInterface */
    private $repository;

    /** @var EntityServiceInterface|MockInterface */
    private $entityService;

    /** @var LoggerInterface|MockInterface */
    private $logger;

    public function setUp()
    {
        $this->repository = m::mock(ServiceRepository::class);
        $this->entityService = m::mock(EntityServiceInterface::class);
        $this->logger = m::mock(LoggerInterface::class);
        $this->commandHandler = new DeleteServiceCommandHandler($this->repository, $this->entityService, $this->logger);
    }

    /**
     * @group CommandHandler
     */
    public function test_it_delete_a_service_with_entities()
    {
        $serviceId = 1;
        $service = m::mock(Service::class);
        $entityList = new EntityList([m::mock(Entity::class), m::mock(Entity::class)]);

        $command = new DeleteServiceCommand($serviceId);

        $this->repository
            ->shouldReceive('findById')
            ->with($serviceId)
            ->andReturn($service)
            ->once();

        $service
            ->shouldReceive('getName')
            ->andReturn('Test SP')
            ->once();

        $this->logger
            ->shouldReceive('info')
            ->times(2);

        $this->entityService
            ->shouldReceive('getEntityListForService')
            ->with($service)
            ->andReturn($entityList)
            ->once();

        $this->entityService
            ->shouldReceive('removeFrom')
            ->with($entityList)
            ->once();

        $this->repository
            ->shouldReceive('delete')
            ->with($service)
            ->once();

        $this->commandHandler->handle($command);
    }

    /**
     * @group CommandHandler
     */
    public function test_it_delete_a_service_without_entities()
    {
        $serviceId = 1;
        $service = m::mock(Service::class);
        $entityList = new EntityList([]);

        $command = new DeleteServiceCommand($serviceId);

        $this->repository
            ->shouldReceive('findById')
            ->with($serviceId)
            ->andReturn($service)
            ->once();

        $service
            ->shouldReceive('getName')
            ->andReturn('Test SP')
            ->once();

        $this->logger
            ->shouldReceive('info')
            ->times();

        $this->entityService
            ->shouldReceive('getEntityListForService')
            ->with($service)
            ->andReturn($entityList)
            ->once();

        $this->repository
            ->shouldReceive('delete')
            ->with($service)
            ->once();

        $this->commandHandler->handle($command);
    }
}
