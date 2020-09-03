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

use League\Tactician\CommandBus;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Psr\Log\LoggerInterface;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\DeleteCommandFactory;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\DeletePublishedTestEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Service\DeleteServiceCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\Service\DeleteServiceCommandHandler;
use Surfnet\ServiceProviderDashboard\Application\Dto\EntityDto;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Application\Service\EntityServiceInterface;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Contact;
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

    /** @var DeleteCommandFactory|MockInterface */
    private $deleteCommandFactory;

    /** @var CommandBus|MockInterface */
    private $commandBus;

    /** @var LoggerInterface|MockInterface */
    private $logger;

    public function setUp()
    {
        $this->repository = m::mock(ServiceRepository::class);
        $this->entityService = m::mock(EntityServiceInterface::class);
        $this->deleteCommandFactory = m::mock(DeleteCommandFactory::class);
        $this->commandBus = m::mock(CommandBus::class);
        $this->logger = m::mock(LoggerInterface::class);

        $this->commandHandler = new DeleteServiceCommandHandler(
            $this->repository,
            $this->entityService,
            $this->deleteCommandFactory,
            $this->commandBus,
            $this->logger
        );
    }

    /**
     * @group CommandHandler
     */
    public function test_it_delete_a_service_with_entities()
    {
        $serviceId = 1;
        $service = m::mock(Service::class);
        $contact = m::mock(Contact::class);

        $entity1 = m::mock(EntityDto::class);
        $entity1->shouldReceive('getEntityId');
        $entity1->shouldReceive('getEnvironment');
        $entity1->shouldReceive('getState');
        $entity1->shouldReceive('setContact')->with($contact);

        $entity2 = m::mock(EntityDto::class);
        $entity2->shouldReceive('getEntityId');
        $entity2->shouldReceive('getEnvironment');
        $entity2->shouldReceive('getState');
        $entity2->shouldReceive('setContact')->with($contact);

        $entityList = [$entity1, $entity2];
        $command = new DeleteServiceCommand($serviceId, $contact);

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
            ->shouldReceive('getEntitiesForService')
            ->with($service)
            ->andReturn($entityList)
            ->once();

        $command2 = m::mock(DeletePublishedTestEntityCommand::class);

      $this->commandBus
            ->shouldReceive('handle')
            ->with($command2)
            ->once();

        $this->deleteCommandFactory
            ->shouldReceive('from')
            ->with($entity2)
            ->andReturn($command2)
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
        $contact = m::mock(Contact::class);

        $entityList = [];

        $command = new DeleteServiceCommand($serviceId, $contact);

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
            ->shouldReceive('getEntitiesForService')
            ->with($service)
            ->andReturn($entityList)
            ->once();

        $this->repository
            ->shouldReceive('delete')
            ->with($service)
            ->once();

        $this->commandHandler->handle($command);
    }

    /**
     * Building the Delete commands for the entities fails on the first command.
     * The second is still executed.
     */
    public function test_unable_to_build_commands_with_factory()
    {
        $serviceId = 1;
        $service = m::mock(Service::class);
        $contact = m::mock(Contact::class);

        $entity1 = m::mock(EntityDto::class);

        $entity1
            ->shouldReceive('getEntityId')
            ->andReturn('https://example.com')
            ->once();

        $entity1
            ->shouldReceive('getState')
            ->andReturn('ohio')
            ->once();

        $entity1
            ->shouldReceive('getEnvironment')
            ->andReturn('polar')
            ->once();

        $entity1
            ->shouldReceive('setContact')
            ->with($contact);

        $entity2 = m::mock(EntityDto::class);
        $entity2->shouldReceive('getEntityId');
        $entity2->shouldReceive('getEnvironment');
        $entity2->shouldReceive('getState');
        $entity2->shouldReceive('setContact')->with($contact);

        $entityList = [$entity1, $entity2];

        $command = new DeleteServiceCommand($serviceId, $contact);

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
            ->shouldReceive('getEntitiesForService')
            ->with($service)
            ->andReturn($entityList)
            ->once();

        $command2 = m::mock(DeletePublishedTestEntityCommand::class);

        $this->deleteCommandFactory
            ->shouldReceive('from')
            ->with($entity1)
            ->andThrow(InvalidArgumentException::class, 'Original exception message for context')
            ->once();

        $this->logger
            ->shouldReceive('error')
            ->with(
                'Removing entity "https://example.com" (env="polar", status="ohio") failed',
                ['Original exception message for context']
            );

        $this->commandBus
            ->shouldReceive('handle')
            ->with($command2)
            ->once();

        $this->deleteCommandFactory
            ->shouldReceive('from')
            ->with($entity2)
            ->andReturn($command2)
            ->once();

        $this->repository
            ->shouldReceive('delete')
            ->with($service)
            ->once();

        $this->commandHandler->handle($command);
    }
}
