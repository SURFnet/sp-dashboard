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
    private $manageClient;

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
        $this->manageClient = m::mock(ManageClient::class);
        $this->attributesMetadataRepository = m::mock(AttributesMetadataRepository::class);

        $this->service = new Service();
        $this->service->setTeamName('testteam');

        $this->commandHandler = new CopyEntityCommandHandler(
            $this->commandBus,
            $this->entityRepository,
            $this->manageClient,
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

        $this->manageClient->shouldReceive('findByManageId')
            ->with('manageid')
            ->andReturn([]);

        $saveCommand = SaveEntityCommand::forCreateAction(m::mock(Service::class));

        $this->commandHandler->handle(
            new CopyEntityCommand(
                $saveCommand,
                'dashboardid',
                'manageid',
                $this->service,
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

        $this->manageClient->shouldReceive('findByManageId')
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
                Entity::ENVIRONMENT_PRODUCTION
            )
        );
    }

    public function test_handler_loads_metadata_onto_new_entity()
    {
        $this->entityRepository->shouldReceive('isUnique')
            ->with('dashboardid')
            ->andReturn(true);

        $this->manageClient->shouldReceive('findByManageId')
            ->with('manageid')
            ->andReturn([
                'data' => [
                    'metaDataFields' => [
                        'name:en' => 'name en',
                        'name:nl' => 'name nl',
                        'description:en' => 'description en',
                        'description:nl' => 'description nl',
                        'coin:service_team_id' => 'testteam',
                        'coin:attr_motivation:eduPersonTargetedID' => 'test1',
                        'coin:attr_motivation:eduPersonPrincipalName' => 'test2',
                        'coin:attr_motivation:displayName' => 'test3',
                    ]
                ]
            ]);

        $this->manageClient->shouldReceive('getMetadataXmlByManageId')
            ->with('manageid')
            ->andReturn('xml');

        $this->commandBus->shouldReceive('handle')
            ->with(m::type(LoadMetadataCommand::class))
            ->andReturn('xml');

        $this->attributesMetadataRepository->shouldReceive('findAllMotivationAttributes')
            ->andReturn(json_decode(<<<JSON
[
  {
    "id": "eduPersonTargetedIDMotivation",
    "getterName": "getEduPersonTargetedIDAttribute",
    "setterName": "setEduPersonTargetedIDAttribute",
    "friendlyName": "EduPersonTargetedIDMotivation",
    "urns": [
      "coin:attr_motivation:eduPersonTargetedID"
    ]
  },
  {
    "id": "eduPersonPrincipalNameMotivation",
    "getterName": "getPrincipleNameAttribute",
    "setterName": "setPrincipleNameAttribute",
    "friendlyName": "EduPersonPrincipalNameMotivation",
    "urns": [
      "coin:attr_motivation:eduPersonPrincipalName"
    ]
  },
  {
    "id": "displayNameMotivation",
    "getterName": "getDisplayNameAttribute",
    "setterName": "setDisplayNameAttribute",
    "friendlyName": "DisplayNameMotivation",
    "urns": [
      "coin:attr_motivation:displayName"
    ]
  }
]
JSON
            ));

        $saveCommand = SaveEntityCommand::forCreateAction(m::mock(Service::class));

        $this->commandHandler->handle(
            new CopyEntityCommand($saveCommand, 'dashboardid', 'manageid', $this->service, Entity::ENVIRONMENT_TEST)
        );

        $this->assertTrue($saveCommand->getEduPersonTargetedIDAttribute()->isRequested());
        $this->assertTrue($saveCommand->getPrincipleNameAttribute()->isRequested());
        $this->assertTrue($saveCommand->getDisplayNameAttribute()->isRequested());
        $this->assertEquals('test1', $saveCommand->getEduPersonTargetedIDAttribute()->getMotivation());
        $this->assertEquals('test2', $saveCommand->getPrincipleNameAttribute()->getMotivation());
        $this->assertEquals('test3', $saveCommand->getDisplayNameAttribute()->getMotivation());
    }
}
