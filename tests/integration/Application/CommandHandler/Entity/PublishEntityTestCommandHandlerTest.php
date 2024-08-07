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

namespace Application\CommandHandler\Entity;

use Surfnet\ServiceProviderDashboard\Application\Service\EntityServiceInterface;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Contact;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\PublishEntityClient;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\QueryClient;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\Mock;
use Psr\Log\LoggerInterface;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\PublishEntityTestCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\Entity\PublishEntityTestCommandHandler;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\PublishMetadataException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class PublishEntityTestCommandHandlerTest extends MockeryTestCase
{
    private PublishEntityTestCommandHandler $commandHandler;


    private LoggerInterface|Mock|m\LegacyMockInterface|m\MockInterface $logger;

    private RequestStack|m\MockInterface|Mock|m\LegacyMockInterface $requestStack;

    private PublishEntityClient|m\MockInterface|m\LegacyMockInterface $client;

    private EntityServiceInterface|m\LegacyMockInterface|m\MockInterface $entityService;

    public function setUp(): void
    {
        $this->client = m::mock(PublishEntityClient::class);
        $this->logger = m::mock(LoggerInterface::class);
        $this->requestStack = m::mock(RequestStack::class);
        $this->entityService = m::mock(EntityServiceInterface::class);

        $this->commandHandler = new PublishEntityTestCommandHandler(
            $this->client,
            $this->entityService,
            $this->logger,
            $this->requestStack
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

        $manageEntity
            ->shouldReceive('getAllowedIdentityProviders->getAllowedIdentityProviders')
            ->andReturn([]);
        $manageEntity
            ->shouldReceive('getAllowedIdentityProviders->isAllowAll')
            ->andReturn(true);

        $manageEntity
            ->shouldReceive('getId')
            ->andReturn(123);
        $manageEntity
            ->shouldReceive('isManageEntity')
            ->andReturn(true);
        $manageEntity->shouldReceive('getId')
            ->andReturn('uuid');
        $manageEntity->shouldReceive('setId');
        $manageEntity
            ->shouldReceive('getEnvironment')
            ->andReturn('test');

        $this->entityService
            ->shouldReceive('getPristineManageEntityById')
            ->andReturn($manageEntity);
        $command = new PublishEntityTestCommand($manageEntity, m::mock(Contact::class));
        $this->client
            ->shouldReceive('publish')
            ->once()
            ->with($manageEntity, $manageEntity, $command->getApplicant())
            ->andReturn([
                'id' => '123',
            ]);
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
        $manageEntity
            ->shouldReceive('isManageEntity')
            ->andReturn(true);
        $manageEntity->shouldReceive('getId')
            ->andReturn('uuid');
        $manageEntity
            ->shouldReceive('getEnvironment')
            ->andReturn('test');
        $this->logger
            ->shouldReceive('info')
            ->times(1);

        $this->logger
            ->shouldReceive('error')
            ->times(1);

        $this->requestStack
            ->shouldReceive('getSession->getFlashBag->add')
            ->with('error', 'entity.edit.error.publish');

        $this->entityService
            ->shouldReceive('getPristineManageEntityById')
            ->andReturn($manageEntity);

        $command = new PublishEntityTestCommand($manageEntity, m::mock(Contact::class));

        $this->client
            ->shouldReceive('publish')
            ->once()
            ->with($manageEntity, $manageEntity, $command->getApplicant())
            ->andThrow(PublishMetadataException::class);

        $this->commandHandler->handle($command);
    }
}
