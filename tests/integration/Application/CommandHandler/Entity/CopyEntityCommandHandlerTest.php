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

use League\Tactician\CommandBus;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\CopyEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\LoadMetadataCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\Entity\CopyEntityCommandHandler;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Repository\AttributesMetadataRepository;
use Surfnet\ServiceProviderDashboard\Domain\Repository\EntityRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\QueryClient as ManageClient;

class CopyEntityCommandHandlerTest extends MockeryTestCase
{
    /**
     * @var CopyEntityCommandHandler
     */
    private $commandHandler;

    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var EntityRepository
     */
    private $entityRepository;

    /**
     * @var ManageClient
     */
    private $manageTestClient;

    /**
     * @var ManageClient
     */
    private $manageProdClient;

    /**
     * @var AttributesMetadataRepository
     */
    private $attributesMetadataRepository;

    /**
     * @var Service
     */
    private $service;

    public function setUp()
    {
        parent::setUp();

        $this->commandBus = m::mock(CommandBus::class);
        $this->entityRepository = m::mock(EntityRepository::class);
        $this->manageTestClient = m::mock(ManageClient::class);
        $this->manageProdClient = m::mock(ManageClient::class);
        $this->attributesMetadataRepository = m::mock(AttributesMetadataRepository::class);

        $this->service = new Service();
        $this->service->setTeamName('testteam');

        $this->commandHandler = new CopyEntityCommandHandler(
            $this->commandBus,
            $this->entityRepository,
            $this->manageTestClient,
            $this->manageProdClient,
            $this->attributesMetadataRepository
        );
    }

    /**
     * @expectedException \Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException
     * @expectedExceptionMessage The id that was generated for the entity was not unique
     */
    public function test_handler_works_on_new_entities_only()
    {
        $this->entityRepository->shouldReceive('isUnique')
            ->with('dashboardid')
            ->andReturn(false);

        $saveCommand = SaveEntityCommand::forCreateAction(m::mock(Service::class));

        $this->commandHandler->handle(
            new CopyEntityCommand(
                $saveCommand,
                'dashboardid',
                'manageid',
                $this->service,
                Entity::ENVIRONMENT_TEST,
                Entity::ENVIRONMENT_TEST
            )
        );
    }

    /**
     * @expectedException \Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException
     * @expectedExceptionMessage Could not find entity in manage: manageid
     */
    public function test_handler_finds_remote_entity_in_manage()
    {
        $this->entityRepository->shouldReceive('isUnique')
            ->with('dashboardid')
            ->andReturn(true);

        $this->manageTestClient->shouldReceive('findByManageId')
            ->with('manageid')
            ->andReturn([]);

        $saveCommand = SaveEntityCommand::forCreateAction(m::mock(Service::class));

        $this->commandHandler->handle(
            new CopyEntityCommand(
                $saveCommand,
                'dashboardid',
                'manageid',
                $this->service,
                Entity::ENVIRONMENT_TEST,
                Entity::ENVIRONMENT_TEST
            )
        );
    }

    /**
     * @expectedException \Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException
     * @expectedExceptionMessage The entity you are about to copy does not belong to the selected team
     */
    public function test_handler_checks_access_rights_of_user()
    {
        $this->entityRepository->shouldReceive('isUnique')
            ->with('dashboardid')
            ->andReturn(true);

        $this->manageProdClient->shouldReceive('findByManageId')
            ->with('manageid')
            ->andReturn([
                'data' => [
                    'metaDataFields' => [
                        'coin:service_team_id' => 'wrongteam',
                    ]
                ]
            ]);

        $saveCommand = SaveEntityCommand::forCreateAction(m::mock(Service::class));
        $this->commandHandler->handle(
            new CopyEntityCommand(
                $saveCommand,
                'dashboardid',
                'manageid',
                $this->service,
                Entity::ENVIRONMENT_PRODUCTION,
                Entity::ENVIRONMENT_PRODUCTION
            )
        );
    }

    public function test_handler_loads_metadata_onto_new_entity_test()
    {
        $this->entityRepository->shouldReceive('isUnique')
            ->with('dashboardid')
            ->andReturn(true);

        $this->manageTestClient->shouldReceive('findByManageId')
            ->with('manageid')
            ->andReturn([
                'data' => [
                    'arp' => [
                        'attributes' => [
                            'urn:mace:dir:attribute-def:eduPersonTargetedID' => [[
                                'source' => 'idp',
                                'value' => '*',
                                'motivation' => 'test1',
                            ]],
                            'urn:mace:dir:attribute-def:eduPersonPrincipalName' => [[
                                'source' => 'idp',
                                'value' => '*',
                                'motivation' => 'test2',
                            ]],
                            'urn:mace:dir:attribute-def:displayName' => [[
                                'source' => 'idp',
                                'value' => '*',
                                'motivation' => 'test3',
                            ]],
                        ],
                    ],
                    'metaDataFields' => [
                        'name:en' => 'name en',
                        'name:nl' => 'name nl',
                        'description:en' => 'description en',
                        'description:nl' => 'description nl',
                        'coin:service_team_id' => 'testteam',
                    ]
                ]
            ]);

        $this->manageTestClient->shouldReceive('getMetadataXmlByManageId')
            ->with('manageid')
            ->andReturn('xml');

        $this->commandBus->shouldReceive('handle')
            ->with(m::type(LoadMetadataCommand::class))
            ->andReturn('xml');

        $this->attributesMetadataRepository->shouldReceive('findAll')
            ->andReturn(json_decode(<<<JSON
[
  {
    "id": "eduPersonTargetedID",
    "getterName": "getEduPersonTargetedIDAttribute",
    "setterName": "setEduPersonTargetedIDAttribute",
    "friendlyName": "eduPersonTargetedID",
    "urns": [
      "urn:mace:dir:attribute-def:eduPersonTargetedID",
      "urn:oid:1.3.6.1.4.1.5923.1.1.1.10"
    ]
  },
  {
    "id": "principleName",
    "getterName": "getPrincipleNameAttribute",
    "setterName": "setPrincipleNameAttribute",
    "friendlyName": "eduPersonPrincipalName",
    "urns": [
      "urn:mace:dir:attribute-def:eduPersonPrincipalName",
      "urn:oid:1.3.6.1.4.1.5923.1.1.1.6"
    ]
  },
  {
    "id": "displayName",
    "getterName": "getDisplayNameAttribute",
    "setterName": "setDisplayNameAttribute",
    "friendlyName": "displayName",
    "urns": [
      "urn:mace:dir:attribute-def:displayName",
      "urn:oid:2.16.840.1.113730.3.1.241"
    ]
  }
]
JSON
            ));

        $saveCommand = SaveEntityCommand::forCreateAction(m::mock(Service::class));

        $this->commandHandler->handle(
            new CopyEntityCommand(
                $saveCommand,
                'dashboardid',
                'manageid',
                $this->service,
                Entity::ENVIRONMENT_TEST,
                Entity::ENVIRONMENT_TEST
            )
        );

        $this->assertTrue($saveCommand->getEduPersonTargetedIDAttribute()->isRequested());
        $this->assertTrue($saveCommand->getPrincipleNameAttribute()->isRequested());
        $this->assertTrue($saveCommand->getDisplayNameAttribute()->isRequested());
        $this->assertEquals('test1', $saveCommand->getEduPersonTargetedIDAttribute()->getMotivation());
        $this->assertEquals('test2', $saveCommand->getPrincipleNameAttribute()->getMotivation());
        $this->assertEquals('test3', $saveCommand->getDisplayNameAttribute()->getMotivation());
    }

    public function test_handler_loads_metadata_onto_new_entity_prod()
    {
        $this->entityRepository->shouldReceive('isUnique')
            ->with('dashboardid')
            ->andReturn(true);

        $this->manageProdClient->shouldReceive('findByManageId')
            ->with('manageid')
            ->andReturn([
                'data' => [
                    'arp' => [
                        'attributes' => [
                            'urn:mace:dir:attribute-def:eduPersonTargetedID' => [[
                                'source' => 'idp',
                                'value' => '*',
                                'motivation' => 'test1',
                            ]],
                            'urn:mace:dir:attribute-def:eduPersonPrincipalName' => [[
                                'source' => 'idp',
                                'value' => '*',
                                'motivation' => 'test2',
                            ]],
                            'urn:mace:dir:attribute-def:displayName' => [[
                                'source' => 'idp',
                                'value' => '*',
                                'motivation' => 'test3',
                            ]],
                        ],
                    ],
                    'metaDataFields' => [
                        'name:en' => 'name en',
                        'name:nl' => 'name nl',
                        'description:en' => 'description en',
                        'description:nl' => 'description nl',
                        'coin:service_team_id' => 'testteam',
                    ]
                ]
            ]);

        $this->manageProdClient->shouldReceive('getMetadataXmlByManageId')
            ->with('manageid')
            ->andReturn('xml');

        $this->commandBus->shouldReceive('handle')
            ->with(m::type(LoadMetadataCommand::class))
            ->andReturn('xml');

        $this->attributesMetadataRepository->shouldReceive('findAll')
            ->andReturn(json_decode(<<<JSON
[
  {
    "id": "eduPersonTargetedID",
    "getterName": "getEduPersonTargetedIDAttribute",
    "setterName": "setEduPersonTargetedIDAttribute",
    "friendlyName": "eduPersonTargetedID",
    "urns": [
      "urn:mace:dir:attribute-def:eduPersonTargetedID",
      "urn:oid:1.3.6.1.4.1.5923.1.1.1.10"
    ]
  },
  {
    "id": "principleName",
    "getterName": "getPrincipleNameAttribute",
    "setterName": "setPrincipleNameAttribute",
    "friendlyName": "eduPersonPrincipalName",
    "urns": [
      "urn:mace:dir:attribute-def:eduPersonPrincipalName",
      "urn:oid:1.3.6.1.4.1.5923.1.1.1.6"
    ]
  },
  {
    "id": "displayName",
    "getterName": "getDisplayNameAttribute",
    "setterName": "setDisplayNameAttribute",
    "friendlyName": "displayName",
    "urns": [
      "urn:mace:dir:attribute-def:displayName",
      "urn:oid:2.16.840.1.113730.3.1.241"
    ]
  }
]
JSON
            ));

        $saveCommand = SaveEntityCommand::forCreateAction(m::mock(Service::class));

        $this->commandHandler->handle(
            new CopyEntityCommand(
                $saveCommand,
                'dashboardid',
                'manageid',
                $this->service,
                Entity::ENVIRONMENT_PRODUCTION,
                Entity::ENVIRONMENT_PRODUCTION
            )
        );

        $this->assertTrue($saveCommand->getEduPersonTargetedIDAttribute()->isRequested());
        $this->assertTrue($saveCommand->getPrincipleNameAttribute()->isRequested());
        $this->assertTrue($saveCommand->getDisplayNameAttribute()->isRequested());
        $this->assertEquals('test1', $saveCommand->getEduPersonTargetedIDAttribute()->getMotivation());
        $this->assertEquals('test2', $saveCommand->getPrincipleNameAttribute()->getMotivation());
        $this->assertEquals('test3', $saveCommand->getDisplayNameAttribute()->getMotivation());
    }
}
