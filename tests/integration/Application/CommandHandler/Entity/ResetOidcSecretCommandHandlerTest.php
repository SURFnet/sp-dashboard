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

namespace Application\CommandHandler\Entity;

use Doctrine\ORM\EntityRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\PublishEntityClient;
use League\Tactician\CommandBus;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\Mock;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\ResetOidcSecretCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\Entity\ResetOidcSecretCommandHandler;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Contact;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\AuthorizationService;

class ResetOidcSecretCommandHandlerTest extends MockeryTestCase
{
    /**
     * @var EntityRepository|Mock
     */
    private $commandBus;

    /**
     * @var PublishEntityClient
     */
    private $publicationClient;

    /**
     * @var ResetOidcSecretCommandHandler
     */
    private $commandHandler;
    /**
     * @var AuthorizationService
     */
    private $authorizationService;

    public function setUp(): void
    {
        $this->commandBus = m::mock(CommandBus::class);
        $this->authorizationService = m::mock(AuthorizationService::class);
        $this->publicationClient = m::mock(PublishEntityClient::class);

        $this->commandHandler = new ResetOidcSecretCommandHandler(
            $this->commandBus,
            $this->authorizationService,
            $this->publicationClient
        );
    }

    public function test_handle_happy_flow()
    {
        $status = Constants::STATE_PUBLISHED;
        $command = $this->buildCommand(Constants::ENVIRONMENT_TEST, $status);
        $this->publicationClient->shouldReceive('pushMetadata');
        $this->commandBus
            ->shouldReceive('handle')
            ->once();
        $this->commandHandler->handle($command);
    }

    public function test_handle_happy_flow_production()
    {
        $status = Constants::STATE_PUBLISHED;
        $command = $this->buildCommand(Constants::ENVIRONMENT_PRODUCTION, $status);
        $this->commandBus
            ->shouldReceive('handle')
            ->once();
        $this->authorizationService
            ->shouldReceive('getContact')
            ->andReturn(m::mock(Contact::class))
            ->once();
        $this->publicationClient
            ->shouldReceive('pushMetadata')
            ->once();
        $this->commandHandler->handle($command);
    }

    public function test_handle_happy_flow_production_excluded_from_push()
    {
        $status = Constants::STATE_PUBLISHED;
        $command = $this->buildCommand(Constants::ENVIRONMENT_PRODUCTION, $status, true);
        $this->commandBus
            ->shouldReceive('handle')
            ->once();
        $this->authorizationService
            ->shouldReceive('getContact')
            ->andReturn(m::mock(Contact::class))
            ->once();
        $this->commandHandler->handle($command);
    }

    public function test_invalid_protocol()
    {
        $status = Constants::STATE_PUBLISHED;
        $command = $this->buildCommand(Constants::ENVIRONMENT_PRODUCTION, $status, false, Constants::TYPE_SAML);
        $command->getManageEntity()
            ->shouldReceive('getProtocol->getProtocol')
            ->andReturn(Constants::TYPE_SAML);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Only OIDC TNG and Oauth CC entities can be processed');
        $this->commandHandler->handle($command);
    }

    /**
     * @return ResetOidcSecretCommand
     */
    private function buildCommand(
        string $environment,
        string $status,
        bool $isExcludedFromPush = false,
        string $protocol = Constants::TYPE_OPENID_CONNECT_TNG
    ) {
        $manageEntity = m::mock(ManageEntity::class);
        $manageEntity
            ->shouldReceive('getEnvironment')
            ->andReturn($environment);
        $manageEntity
            ->shouldReceive('getProtocol->getProtocol')
            ->andReturn($protocol);
        $manageEntity
            ->shouldReceive('getStatus')
            ->andReturn($status);
        $manageEntity
            ->shouldReceive('updateClientSecret');
        $manageEntity
            ->shouldReceive('isExcludedFromPush')
            ->andReturn($isExcludedFromPush);

        return new ResetOidcSecretCommand($manageEntity);
    }
}
