<?php

/**
 * Copyright 2019 SURFnet B.V.
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
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\ResetOidcSecretCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\Entity\ResetOidcSecretCommandHandler;
use Surfnet\ServiceProviderDashboard\Application\Exception\EntityNotFoundException;
use Surfnet\ServiceProviderDashboard\Application\Service\LoadEntityService;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Repository\EntityRepository;

class ResetOidcSecretCommandHandlerTest extends MockeryTestCase
{
    /**
     * @var EntityRepository|Mock
     */
    private $repository;
    /**
     * @var LoadEntityService|Mock
     */
    private $loadEntityService;

    /**
     * @var ResetOidcSecretCommandHandler
     */
    private $commandHandler;

    public function setUp()
    {
        $this->repository = m::mock(EntityRepository::class);
        $this->loadEntityService = m::mock(LoadEntityService::class);

        $this->commandHandler = new ResetOidcSecretCommandHandler(
            $this->repository,
            $this->loadEntityService
        );
    }

    public function test_handle_happy_flow()
    {
        $status = Entity::STATE_PUBLISHED;
        $command = $this->buildCommand(1, 'my-manage-id', Entity::ENVIRONMENT_TEST);

        $entity = $this->expectedEntityFrom($command, $status);

        $this->repository
            ->shouldReceive('save')
            ->with($entity);

        $this->commandHandler->handle($command);

        $this->assertEquals('my-manage-id', $entity->getManageId());
    }

    public function test_handle_entity_not_found()
    {
        $command = $this->buildCommand(1, 'my-manage-id', Entity::ENVIRONMENT_TEST);

        $this->loadEntityService
            ->shouldReceive('load')
            ->with(
                $command->getId(),
                $command->getManageId(),
                $command->getService(),
                $command->getEnvironment(),
                $command->getEnvironment()
            )
            ->andReturn(null);

        $this->expectException(EntityNotFoundException::class);
        $this->expectExceptionMessage('The requested entity could not be found');

        $this->commandHandler->handle($command);
    }

    public function test_handle_entity_invalid_protocol()
    {
        $status = Entity::STATE_PUBLISHED;
        $command = $this->buildCommand(1, 'my-manage-id', Entity::ENVIRONMENT_TEST);

        $entity = $this->expectedEntityFrom($command, $status);
        $entity->setProtocol('oauth');

        $this->expectException(EntityNotFoundException::class);
        $this->expectExceptionMessage('The requested entity could be found, invalid protocol');

        $this->commandHandler->handle($command);
    }

    public function test_handle_entity_invalid_publication_status()
    {
        $status = Entity::STATE_PUBLISHED;
        $command = $this->buildCommand(1, 'my-manage-id', Entity::ENVIRONMENT_TEST);

        $entity = $this->expectedEntityFrom($command, $status);
        $entity->setStatus(Entity::STATE_DRAFT);

        $this->expectException(EntityNotFoundException::class);
        $this->expectExceptionMessage('The requested entity could be found, invalid state');

        $this->commandHandler->handle($command);
    }

    /**
     * @return ResetOidcSecretCommand
     */
    private function buildCommand($id, $manageId, $environment)
    {
        return new ResetOidcSecretCommand($id, $manageId, $environment, m::mock(Service::class));
    }

    /**
     * @param ResetOidcSecretCommand $command
     * @param string $status
     * @return Entity
     */
    private function expectedEntityFrom(ResetOidcSecretCommand $command, $status)
    {
        $entity = new Entity();
        $entity->setId($command->getId());
        $entity->setService($command->getService());
        $entity->setStatus($status);
        $entity->setEnvironment($entity);
        $entity->setProtocol(Entity::TYPE_OPENID_CONNECT);


        $this->loadEntityService
            ->shouldReceive('load')
            ->with(
                $command->getId(),
                $command->getManageId(),
                $command->getService(),
                $command->getEnvironment(),
                $command->getEnvironment()
            )
            ->andReturn($entity);

        return $entity;
    }
}
