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
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\AllowedIdentityProviders;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\AttributeList;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Coin;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\ContactList;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Logo;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\MetaData;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Organization;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Protocol;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Service\ContractualBaseService;
use Surfnet\ServiceProviderDashboard\Domain\Service\TypeOfServiceService;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\TypeOfService;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\TypeOfServiceCollection;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\PublishEntityClient;
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
            new ContractualBaseService(),
            new TypeOfServiceService(),
            $this->entityService,
            $this->logger,
            $this->requestStack
        );

        parent::setUp();
    }

    public function test_it_can_publish_to_manage()
    {
        $manageEntity = m::mock(ManageEntity::class);
        $metaData = m::mock(MetaData::class);
        $coin = m::mock(Coin::class);

        $manageEntity
            ->shouldReceive('getMetaData')
            ->andReturn($metaData);

        $metaData
            ->shouldReceive('getNameEn')
            ->andReturn('Test Entity Name')
            ->shouldReceive('getManageId')
            ->shouldReceive('getProtocol')
            ->andReturn(Constants::TYPE_OPENID_CONNECT_TNG)
            ->shouldReceive('getCoin')
            ->andReturn($coin);

        $coin
            ->shouldReceive('getContractualBase')
            ->shouldReceive('hasTypeOfService')->andReturn(false)
            ->shouldReceive('setTypeOfService')
            ->andReturn('some_contractual_base_value');

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
            ->shouldReceive('getEnvironment');

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
        $metaData = m::mock(MetaData::class);
        $coin = m::mock(Coin::class);

        $manageEntity
            ->shouldReceive('getMetaData')
            ->andReturn($metaData);

        $metaData
            ->shouldReceive('getNameEn')
            ->andReturn('Test Entity Name')
            ->shouldReceive('getManageId')
            ->shouldReceive('getProtocol')
            ->andReturn(Constants::TYPE_OPENID_CONNECT_TNG)
            ->shouldReceive('getCoin')
            ->andReturn($coin);

        $coin
            ->shouldReceive('getContractualBase')
            ->shouldReceive('hasTypeOfService')->andReturn(false)
            ->shouldReceive('setTypeOfService')
            ->andReturn('some_contractual_base_value');

        $coin
            ->shouldReceive('setContractualBase')
            ->with('some_contractual_base_value');

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

    public function test_it_should_ensure_hidden_type_of_service_selections_are_preserved()
    {
        $manageEntity = $this->createEntity();

        $applicant = new Contact('john:doe', 'john@example.com', 'John Doe');

        /** @var ManageEntity $pristineEntity */
        $pristineEntity = unserialize(serialize($manageEntity));

        $pristineEntityCoin = $pristineEntity->getMetaData()->getCoin()->getTypeOfService();
        $manageEntityCoin = $manageEntity->getMetaData()->getCoin()->getTypeOfService();

        $pristineEntityCoin->add(new TypeOfService('SURF', 'SURF', true));
        $pristineEntityCoin->add(new TypeOfService('Education', 'Onderwijs', false));

        $manageEntityCoin->add(new TypeOfService('Productivity', 'Productiviteit', false));
        $manageEntityCoin->add(new TypeOfService('Privacy/security', 'Privacy/beveiliging', false));
        $manageEntityCoin->add(new TypeOfService('Recommended', 'Aangeraden', true));
        $manageEntityCoin->add(new TypeOfService('Productivity', 'Productiviteit', false));
        $this->logger
            ->shouldReceive('info')
            ->times(1);

        $this->entityService->shouldReceive('getPristineManageEntityById')
            ->once()
            ->with($manageEntity->getId(), $manageEntity->getEnvironment())
            ->andReturn($pristineEntity);

        $expectedTypeOfService = new TypeOfServiceCollection();
        $expectedTypeOfService->add(new TypeOfService('Productivity', 'Productiviteit', false));
        $expectedTypeOfService->add(new TypeOfService('Privacy/security', 'Privacy/beveiliging', false));
        $expectedTypeOfService->add(new TypeOfService('SURF', 'SURF', true));

        $command = new PublishEntityTestCommand($manageEntity, $applicant);

        $this->client
            ->shouldReceive('publish')
            ->once()
            ->withArgs(function (ManageEntity $entity, $pristineEntity, $applicant) use ($expectedTypeOfService){
                if ($expectedTypeOfService->getArray() != $entity->getMetaData()->getCoin()->getTypeOfService()->getArray()) {
                    $this->fail(
                        var_export($entity->getMetaData()->getCoin()->getTypeOfService()->getArray(), true) . ' is not equal to ' . var_export(
                            $expectedTypeOfService->getArray(),
                            true
                        )
                    );
                }
                return true;
            })
            ->andReturn([
                'id' => '123',
            ]);

        $this->commandHandler->handle($command);
    }

    private function createEntity(): ManageEntity
    {
        $coin = new Coin(
            null,
            null,
            null,
            null,
            null,
            new TypeOfServiceCollection(),
            null,
            null,
            null,
            null
        );

        $manageEntity = new ManageEntity(
            1,
            new AttributeList(),
            new MetaData(
                'https://test.entity.id',
                'https://test.metadata.url',
                null,
                null,
                'Test Description',
                null,
                'Test Entity',
                null,
                new ContactList(),
                new Organization('Test org', "Test organisation", null, null, null, null),
                $coin,
                new Logo(null, null, null)
            ),
            new AllowedIdentityProviders([], true), new Protocol(Constants::TYPE_SAML),
            null,
            new Service()
        );
        $manageEntity->setId('f1e394b2-815b-4018-b780-898976322016');
        $manageEntity->setEnvironment('production');
        $manageEntity->setStatus('published');
        return $manageEntity;
    }
}
