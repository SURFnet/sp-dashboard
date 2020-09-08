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
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\PublishEntityTestCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\Entity\PublishEntityTestCommandHandler;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\PublishEntityClient;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\QueryClient;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Exception\PublishMetadataException;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class PublishEntityTestCommandHandlerTest extends MockeryTestCase
{
    /**
     * @var PublishEntityTestCommandHandler
     */
    private $commandHandler;

    /**
     * @var LoggerInterface|Mock
     */
    private $logger;

    /**
     * @var FlashBagInterface|Mock
     */
    private $flashBag;

    /**
     * @var PublishEntityClient
     */
    private $client;

    /**
     * @var m\MockInterface&QueryClient
     */
    private $manageClient;

    public function setUp()
    {
        $this->client = m::mock(PublishEntityClient::class);
        $this->manageClient = m::mock(QueryClient::class);
        $this->logger = m::mock(LoggerInterface::class);
        $this->flashBag = m::mock(FlashBagInterface::class);

        $this->commandHandler = new PublishEntityTestCommandHandler(
            $this->client,
            $this->manageClient,
            $this->logger,
            $this->flashBag
        );

        parent::setUp();
    }

    public function test_it_can_publish_to_manage()
    {
        $manageEntity = m::mock(ManageEntity::class);
        $manageEntity
            ->shouldReceive('getMetaData->getNameEn')
            ->andReturn('Test Entity Name')
            ->shouldReceive('geMetaData->getManageId')
            ->shouldReceive('getProtocol->geProtocol')
            ->shouldReceive('setIdpAllowAll')
            ->shouldReceive('setIdpWhitelistRaw')
            ->andReturn(Constants::TYPE_OPENID_CONNECT_TNG);

        $this->logger
            ->shouldReceive('info')
            ->times(1);

        $this->client
            ->shouldReceive('publish')
            ->once()
            ->with($manageEntity)
            ->andReturn([
                'id' => '123',
            ]);
        $manageEntity
            ->shouldReceive('getAllowedIdentityProviders->getAllowedIdentityProviders')
            ->andReturn([]);
        $manageEntity
            ->shouldReceive('getAllowedIdentityProviders->isAllowAll')
            ->andReturn(true);

        $manageEntity
            ->shouldReceive('getId')
            ->andReturn(123);

        $this->manageClient
            ->shouldReceive('findByManageId')
            ->andReturn($manageEntity);

        $command = new PublishEntityTestCommand($manageEntity);
        $this->commandHandler->handle($command);
    }

    public function test_it_handles_failing_publish()
    {
        $manageEntity = m::mock(ManageEntity::class);
        $manageEntity
            ->shouldReceive('getMetaData->getNameEn')
            ->andReturn('Test Entity Name')
            ->shouldReceive('geMetaData->getManageId')
            ->shouldReceive('getProtocol->geProtocol')
            ->shouldReceive('setIdpAllowAll')
            ->shouldReceive('setIdpWhitelistRaw')
            ->andReturn(Constants::TYPE_OPENID_CONNECT_TNG);

        $manageEntity
            ->shouldReceive('getAllowedIdentityProviders->getAllowedIdentityProviders')
            ->andReturn([]);
        $manageEntity
            ->shouldReceive('getAllowedIdentityProviders->isAllowAll')
            ->andReturn(true);

        $this->manageClient
            ->shouldReceive('findByManageId')
            ->andReturn($manageEntity);

        $this->logger
            ->shouldReceive('info')
            ->times(1);

        $this->logger
            ->shouldReceive('error')
            ->times(1);

        $this->client
            ->shouldReceive('publish')
            ->once()
            ->with($manageEntity)
            ->andThrow(PublishMetadataException::class);

        $this->flashBag
            ->shouldReceive('add')
            ->with('error', 'entity.edit.error.publish');

        $command = new PublishEntityTestCommand($manageEntity);
        $this->commandHandler->handle($command);
    }
}
