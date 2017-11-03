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

namespace Surfnet\ServiceProviderDashboard\Tests\Integration\Application\CommandHandler\Entity;

use League\Tactician\CommandBus;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\Mock;
use Psr\Log\LoggerInterface;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\PublishEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\PublishEntityProductionCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\Entity\PublishEntityProductionCommandHandler;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Mailer\Message;
use Surfnet\ServiceProviderDashboard\Domain\Repository\EntityRepository;

class PublishEntityProductionCommandHandlerTest extends MockeryTestCase
{

    /**
     * @var PublishEntityProductionCommandHandler
     */
    private $commandHandler;

    /**
     * @var EntityRepository|Mock
     */
    private $repository;

    /**
     * @var CommandBus|Mock
     */
    private $commandBus;

    /**
     * @var LoggerInterface|Mock
     */
    private $logger;

    public function setUp()
    {

        $this->logger = m::mock(LoggerInterface::class);
        $this->repository = m::mock(EntityRepository::class);
        $this->commandBus = m::mock(CommandBus::class);

        $this->commandHandler = new PublishEntityProductionCommandHandler(
            $this->repository,
            $this->commandBus,
            $this->logger
        );


        parent::setUp();
    }

    public function test_it_can_publish()
    {
        $entity = m::mock(Entity::class);

        $entity
            ->shouldReceive('getNameEn')
            ->andReturn('Test Entity Name');

        $entity
            ->shouldReceive('setStatus')
            ->with(Entity::STATE_PUBLISHED);

        $this->repository
            ->shouldReceive('findById')
            ->with('d6f394b2-08b1-4882-8b32-81688c15c489')
            ->andReturn($entity);

        $this->repository
            ->shouldReceive('save')
            ->with($entity);

        $this->commandBus
            ->shouldReceive('handle')
            ->once();

        $this->logger
            ->shouldReceive('info')
            ->times(2);

        $message = m::mock(Message::class);

        $command = new PublishEntityProductionCommand('d6f394b2-08b1-4882-8b32-81688c15c489', $message);
        $this->commandHandler->handle($command);
    }
}
