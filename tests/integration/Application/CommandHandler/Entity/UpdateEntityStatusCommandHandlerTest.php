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
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\UpdateEntityStatusCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\Entity\UpdateEntityStatusCommandHandler;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Repository\EntityRepository;

class UpdateEntityStatusCommandHandlerTest extends MockeryTestCase
{
    /**
     * @var UpdateEntityStatusCommandHandler
     */
    private $commandHandler;

    /**
     * @var EntityRepository
     */
    private $entityRepository;

    public function setUp()
    {
        parent::setUp();

        $this->entityRepository = m::mock(EntityRepository::class);

        $this->commandHandler = new UpdateEntityStatusCommandHandler(
            $this->entityRepository
        );
    }

    public function test_handler_updates_status()
    {
        $entity = m::mock(Entity::class);

        $entity
            ->shouldReceive('setStatus')
            ->with(Entity::STATE_DRAFT);

        $this->entityRepository
            ->shouldReceive('findById')
            ->with('my-entity-id')
            ->andReturn($entity);

        $this->entityRepository
            ->shouldReceive('save')
            ->with($entity);

        $command = new UpdateEntityStatusCommand('my-entity-id', Entity::STATE_DRAFT);
        $this->commandHandler->handle($command);
    }
}
