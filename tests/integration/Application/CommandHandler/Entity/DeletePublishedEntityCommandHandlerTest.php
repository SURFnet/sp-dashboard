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

namespace Surfnet\ServiceProviderDashboard\Tests\Integration\Application\CommandHandler\Entity;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\Mock;
use Psr\Log\LoggerInterface;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\DeletePublishedEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\Entity\DeletePublishedEntityCommandHandler;
use Surfnet\ServiceProviderDashboard\Application\Exception\UnableToDeleteEntityException;
use Surfnet\ServiceProviderDashboard\Domain\Repository\DeleteEntityRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Exception\DeleteEntityFromManageException;

class DeletePublishedEntityCommandHandlerTest extends MockeryTestCase
{

    /**
     * @var DeletePublishedEntityCommandHandler
     */
    private $commandHandler;

    /**
     * @var DeleteEntityRepository|Mock
     */
    private $repositoryTest;
    /**
     * @var DeleteEntityRepository|Mock
     */
    private $repositoryProd;

    /**
     * @var LoggerInterface|Mock
     */
    private $logger;

    public function setUp()
    {
        $this->repositoryTest = m::mock(DeleteEntityRepository::class);
        $this->repositoryProd = m::mock(DeleteEntityRepository::class);

        $this->logger = m::mock(LoggerInterface::class);

        $this->commandHandler = new DeletePublishedEntityCommandHandler(
            $this->repositoryTest,
            $this->repositoryProd,
            $this->logger
        );
    }

    public function test_it_can_delete_an_entity_from_test()
    {
        $command = new DeletePublishedEntityCommand('d6f394b2-08b1-4882-8b32-81688c15c489', 'test');

        $this->repositoryTest
            ->shouldReceive('delete')
            ->with('d6f394b2-08b1-4882-8b32-81688c15c489')
            ->andReturn(DeleteEntityRepository::RESULT_SUCCESS);

        $this->logger
            ->shouldReceive('info');

        $this->commandHandler->handle($command);
    }

    public function test_it_can_delete_an_entity_from_production()
    {
        $command = new DeletePublishedEntityCommand('d6f394b2-08b1-4882-8b32-81688c15c489', 'production');

        $this->repositoryProd
            ->shouldReceive('delete')
            ->with('d6f394b2-08b1-4882-8b32-81688c15c489')
            ->andReturn(DeleteEntityRepository::RESULT_SUCCESS);

        $this->logger
            ->shouldReceive('info');

        $this->commandHandler->handle($command);
    }

    /**
     * @expectedException \Surfnet\ServiceProviderDashboard\Application\Exception\EntityNotDeletedException
     * @expectedExceptionMessage Deleting the entity yielded an non success response
     */
    public function test_it_handles_non_error_responses()
    {
        $command = new DeletePublishedEntityCommand('d6f394b2-08b1-4882-8b32-81688c15c489', 'production');

        $this->repositoryProd
            ->shouldReceive('delete')
            ->with('d6f394b2-08b1-4882-8b32-81688c15c489')
            ->andReturn(false);

        $this->logger
            ->shouldReceive('info');

        $this->commandHandler->handle($command);
    }

    /**
     * @expectedException \Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException
     * @expectedExceptionMessage Deleting entities from "staging" environment is not supported
     */
    public function test_it_rejects_invalid_environment()
    {
        $command = new DeletePublishedEntityCommand('d6f394b2-08b1-4882-8b32-81688c15c489', 'staging');

        $this->logger
            ->shouldReceive('info');

        $this->commandHandler->handle($command);
    }

    /**
     * @expectedException \Surfnet\ServiceProviderDashboard\Application\Exception\EntityNotDeletedException
     */
    public function test_it_handles_failing_delete_requests()
    {
        $command = new DeletePublishedEntityCommand('d6f394b2-08b1-4882-8b32-81688c15c489', 'test');

        $this->repositoryTest
            ->shouldReceive('delete')
            ->with('d6f394b2-08b1-4882-8b32-81688c15c489')
            ->andThrow(UnableToDeleteEntityException::class);

        $this->logger
            ->shouldReceive('info');

        $this->commandHandler->handle($command);
    }
}
