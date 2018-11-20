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

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\Mock;
use Psr\Log\LoggerInterface;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\DeleteDraftEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\Entity\DeleteDraftEntityCommandHandler;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Repository\EntityRepository;

class DeleteDraftEntityCommandHandlerTest extends MockeryTestCase
{

    /**
     * @var DeleteDraftEntityCommandHandler
     */
    private $commandHandler;

    /**
     * @var EntityRepository|Mock
     */
    private $repository;

    /**
     * @var LoggerInterface|Mock
     */
    private $logger;

    public function setUp()
    {
        $this->repository = m::mock(EntityRepository::class);

        $this->logger = m::mock(LoggerInterface::class);

        $this->commandHandler = new DeleteDraftEntityCommandHandler($this->repository, $this->logger);
    }

    public function test_it_can_delete_an_entity()
    {
        $command = new DeleteDraftEntityCommand('d6f394b2-08b1-4882-8b32-81688c15c489');

        $entity = m::mock(Entity::class);
        $entity->shouldReceive('getNameEn');

        $this->repository
            ->shouldReceive('findById')
            ->with('d6f394b2-08b1-4882-8b32-81688c15c489')
            ->andReturn($entity);

        $this->repository
            ->shouldReceive('delete')
            ->with($entity);

        $this->logger
            ->shouldReceive('info');

        $this->commandHandler->handle($command);
    }
}
