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
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\DeletePublishedTestEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\Entity\DeletePublishedTestEntityCommandHandler;
use Surfnet\ServiceProviderDashboard\Application\Exception\UnableToDeleteEntityException;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Repository\DeleteManageEntityRepository;
use Surfnet\ServiceProviderDashboard\Domain\Repository\PublishEntityRepository;

class DeletePublishedTestEntityCommandHandlerTest extends MockeryTestCase
{

    /**
     * @var DeletePublishedTestEntityCommandHandler
     */
    private $commandHandler;

    /**
     * @var DeleteManageEntityRepository|Mock
     */
    private $repository;

    /**
     * @var LoggerInterface&Mock
     */
    private $logger;

    private PublishEntityRepository $publishClient;

    public function setUp(): void
    {
        $this->repository = m::mock(DeleteManageEntityRepository::class);

        $this->logger = m::mock(LoggerInterface::class);

        $this->publishClient = m::mock(PublishEntityRepository::class);

        $this->commandHandler = new DeletePublishedTestEntityCommandHandler(
            $this->repository,
            $this->publishClient,
            $this->logger
        );
    }

    public function test_it_can_delete_an_entity_from_test()
    {
        $command = new DeletePublishedTestEntityCommand('d6f394b2-08b1-4882-8b32-81688c15c489', Constants::TYPE_SAML);

        $this->publishClient
            ->shouldReceive('pushMetadata')
            ->once();

        $this->repository
            ->shouldReceive('delete')
            ->with('d6f394b2-08b1-4882-8b32-81688c15c489', Constants::TYPE_SAML)
            ->andReturn(DeleteManageEntityRepository::RESULT_SUCCESS);

        $this->logger
            ->shouldReceive('info');

        $this->assertNull($this->commandHandler->handle($command));
    }

    public function test_it_handles_non_error_responses()
    {
        $command = new DeletePublishedTestEntityCommand(
            'd6f394b2-08b1-4882-8b32-81688c15c489',
            Constants::TYPE_OPENID_CONNECT_TNG
        );

        $this->repository
            ->shouldReceive('delete')
            ->with('d6f394b2-08b1-4882-8b32-81688c15c489', Constants::TYPE_OPENID_CONNECT_TNG)
            ->andReturn(false);

        $this->logger
            ->shouldReceive('info');

        $this->expectExceptionMessage("Deleting the entity yielded an non success response");
        $this->expectException(
            \Surfnet\ServiceProviderDashboard\Application\Exception\EntityNotDeletedException::class
        );
        $this->commandHandler->handle($command);
    }

    public function test_it_handles_failing_delete_requests()
    {
        $command = new DeletePublishedTestEntityCommand(
            'd6f394b2-08b1-4882-8b32-81688c15c489',
            Constants::TYPE_OPENID_CONNECT_TNG
        );

        $this->repository
            ->shouldReceive('delete')
            ->with('d6f394b2-08b1-4882-8b32-81688c15c489', Constants::TYPE_OPENID_CONNECT_TNG)
            ->andThrow(UnableToDeleteEntityException::class);

        $this->logger
            ->shouldReceive('info');

        $this->expectException(
            \Surfnet\ServiceProviderDashboard\Application\Exception\EntityNotDeletedException::class
        );
        $this->commandHandler->handle($command);
    }
}
