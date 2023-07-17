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

namespace Application\CommandHandler\Entity;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\Mock;
use Psr\Log\LoggerInterface;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\DeletePublishedProductionEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\Entity\DeletePublishedProductionEntityCommandHandler;
use Surfnet\ServiceProviderDashboard\Application\Exception\EntityNotDeletedException;
use Surfnet\ServiceProviderDashboard\Application\Exception\UnableToDeleteEntityException;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Repository\DeleteManageEntityRepository;

class DeletePublishedProductionEntityCommandHandlerTest extends MockeryTestCase
{

    /**
     * @var DeletePublishedProductionEntityCommandHandler
     */
    private $commandHandler;

    /**
     * @var DeleteManageEntityRepository|Mock
     */
    private $repository;

    /**
     * @var LoggerInterface|Mock
     */
    private $logger;

    public function setUp(): void
    {
        $this->repository = m::mock(DeleteManageEntityRepository::class);

        $this->logger = m::mock(LoggerInterface::class);

        $this->commandHandler = new DeletePublishedProductionEntityCommandHandler(
            $this->repository,
            $this->logger
        );
    }

    public function test_it_can_delete_an_entity_from_production()
    {
        $command = new DeletePublishedProductionEntityCommand(
            'd6f394b2-08b1-4882-8b32-81688c15c489',
            Constants::TYPE_OPENID_CONNECT_TNG
        );

        $this->repository
            ->shouldReceive('delete')
            ->with('d6f394b2-08b1-4882-8b32-81688c15c489', Constants::TYPE_OPENID_CONNECT_TNG)
            ->andReturn(DeleteManageEntityRepository::RESULT_SUCCESS);

        $this->logger
            ->shouldReceive('info');

        $this->assertNull($this->commandHandler->handle($command));
    }

    public function test_it_handles_non_error_responses()
    {
        $command = new DeletePublishedProductionEntityCommand(
            'd6f394b2-08b1-4882-8b32-81688c15c489',
            Constants::TYPE_SAML
        );

        $this->repository
            ->shouldReceive('delete')
            ->with('d6f394b2-08b1-4882-8b32-81688c15c489', Constants::TYPE_SAML)
            ->andReturn(false);

        $this->logger
            ->shouldReceive('info');

        $this->expectExceptionMessage("Deleting the entity yielded a non success response");
        $this->expectException(EntityNotDeletedException::class);
        $this->commandHandler->handle($command);
    }

    public function test_it_handles_failing_delete_requests()
    {
        $command = new DeletePublishedProductionEntityCommand(
            'd6f394b2-08b1-4882-8b32-81688c15c489',
            Constants::TYPE_OPENID_CONNECT_TNG
        );

        $this->repository
            ->shouldReceive('delete')
            ->with('d6f394b2-08b1-4882-8b32-81688c15c489', Constants::TYPE_OPENID_CONNECT_TNG)
            ->andThrow(UnableToDeleteEntityException::class);

        $this->logger
            ->shouldReceive('info');

        $this->expectException(EntityNotDeletedException::class);
        $this->commandHandler->handle($command);
    }
}
