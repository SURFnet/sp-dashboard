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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Application\Service;

use League\Tactician\CommandBus;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Surfnet\ServiceProviderDashboard\Application\Service\LoadEntityService;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Repository\AttributesMetadataRepository;
use Surfnet\ServiceProviderDashboard\Domain\Repository\EntityRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\QueryClient as ManageClient;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Dto\Coin;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Dto\ManageEntity;

class LoadEntityServiceTest extends MockeryTestCase
{
    /**
     * @var LoadEntityService
     */
    private $copyService;

    /**
     * @var EntityRepository|m\Mock
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

    const OIDC_PLAYGROUN_URL_TEST = 'http://playground-test';
    const OIDC_PLAYGROUN_URL_PROD = 'http://playground-prod';

    public function setUp()
    {
        parent::setUp();

        $this->entityRepository = m::mock(EntityRepository::class);
        $this->manageTestClient = m::mock(ManageClient::class);
        $this->manageProdClient = m::mock(ManageClient::class);
        $this->attributesMetadataRepository = m::mock(AttributesMetadataRepository::class);

        $this->service = new Service();
        $this->service->setTeamName('testteam');

        $this->copyService = new LoadEntityService(
            $this->entityRepository,
            $this->manageTestClient,
            $this->manageProdClient,
            $this->attributesMetadataRepository,
            self::OIDC_PLAYGROUN_URL_TEST,
            self::OIDC_PLAYGROUN_URL_PROD
        );
    }

    /**
     * @expectedException \Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException
     * @expectedExceptionMessage The id that was generated for the entity was not unique
     */
    public function test_service_works_on_new_entities_only()
    {
        $this->entityRepository->shouldReceive('isUnique')
            ->with('dashboardid')
            ->andReturn(false);

        $this->copyService->load(
            'dashboardid',
            'manageid',
            $this->service,
            Entity::ENVIRONMENT_TEST,
            Entity::ENVIRONMENT_TEST
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

        $this->copyService->load(
            'dashboardid',
            'manageid',
            $this->service,
            Entity::ENVIRONMENT_TEST,
            Entity::ENVIRONMENT_TEST
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

        $manageEntity = m::mock(ManageEntity::class);

        $coin = m::mock(Coin::class);

        $manageEntity
            ->shouldReceive('getMetaData->getCoin')
            ->andReturn($coin);

        $coin
            ->shouldReceive('getServiceTeamId')
            ->andReturn('wrongteam');
        $coin
            ->shouldReceive('getExcludeFromPush')
            ->andReturn(1);

        $this->manageProdClient->shouldReceive('findByManageId')
            ->with('manageid')
            ->andReturn($manageEntity);

        $this->copyService->load(
            'dashboardid',
            'manageid',
            $this->service,
            Entity::ENVIRONMENT_PRODUCTION,
            Entity::ENVIRONMENT_PRODUCTION
        );
    }

    public function test_handler_loads_metadata_onto_new_entity_test()
    {
        $this->entityRepository->shouldReceive('isUnique')
            ->with('dashboardid')
            ->andReturn(true);

        $this->entityRepository->shouldReceive('save')
            ->with((\Mockery::on(function ($savedEntity) {
                /** @var $savedEntity Entity|null */
                $this->assertTrue($savedEntity->getEduPersonTargetedIDAttribute()->isRequested());
                $this->assertTrue($savedEntity->getPrincipleNameAttribute()->isRequested());
                $this->assertTrue($savedEntity->getDisplayNameAttribute()->isRequested());
                $this->assertEquals('test1', $savedEntity->getEduPersonTargetedIDAttribute()->getMotivation());
                $this->assertEquals('test2', $savedEntity->getPrincipleNameAttribute()->getMotivation());
                $this->assertEquals('test3', $savedEntity->getDisplayNameAttribute()->getMotivation());

                return true;
            })));

        $manageDto = ManageEntity::fromApiResponse([
            'id' => '161438a5-50ae-49a6-8ce4-88ea44eef68d',
            'data' => [
                'entityid' => 'http://example.com',
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

        $this->manageTestClient->shouldReceive('findByManageId')
            ->with('manageid')
            ->andReturn($manageDto);

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

        $this->copyService->load(
            'dashboardid',
            'manageid',
            $this->service,
            Entity::ENVIRONMENT_TEST,
            Entity::ENVIRONMENT_TEST
        );
    }

    public function test_handler_loads_metadata_onto_new_entity_prod()
    {
        $this->entityRepository->shouldReceive('isUnique')
            ->with('dashboardid')
            ->andReturn(true);

        $this->entityRepository->shouldReceive('save')
            ->with((\Mockery::on(function ($savedEntity) {
                /** @var $savedEntity Entity|null */
                $this->assertTrue($savedEntity->getEduPersonTargetedIDAttribute()->isRequested());
                $this->assertTrue($savedEntity->getPrincipleNameAttribute()->isRequested());
                $this->assertTrue($savedEntity->getDisplayNameAttribute()->isRequested());
                $this->assertEquals('test1', $savedEntity->getEduPersonTargetedIDAttribute()->getMotivation());
                $this->assertEquals('test2', $savedEntity->getPrincipleNameAttribute()->getMotivation());
                $this->assertEquals('test3', $savedEntity->getDisplayNameAttribute()->getMotivation());

                return true;
            })));

        $manageDto = ManageEntity::fromApiResponse([
            'id' => '161438a5-50ae-49a6-8ce4-88ea44eef68d',
            'data' => [
                'entityid' => 'http://example.com',
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

        $this->manageProdClient->shouldReceive('findByManageId')
            ->with('manageid')
            ->andReturn($manageDto);

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

        $this->copyService->load(
            'dashboardid',
            'manageid',
            $this->service,
            Entity::ENVIRONMENT_PRODUCTION,
            Entity::ENVIRONMENT_PRODUCTION
        );
    }

    /**
     * @dataProvider providePlaygroundUrls
     */
    public function test_handler_should_handle_playground_redirect_url_for_production($testName, $redirectUris, $sourceEnviroment, $destinationEvironment, $excpectedUris, $expectedEnabledPlayground)
    {
        $this->entityRepository->shouldReceive('isUnique')
            ->with('dashboardid')
            ->andReturn(true);

        $manageDto = ManageEntity::fromApiResponse([
            'id' => '161438a5-50ae-49a6-8ce4-88ea44eef68d',
            'data' => [
                'entityid' => 'http://example.com',
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
                    'coin:oidc_client' => '1',
                ],
                'oidcClient' => [
                    'clientId' => 'http@//entityid',
                    'clientSecret' => 'test',
                    'redirectUris' => $redirectUris,
                    'grantType' => 'implicit',
                    'scope' => ['openid'],
                ],
            ],
        ]);

        $this->manageProdClient->shouldReceive('findByManageId')
            ->with('manageid')
            ->andReturn($manageDto);

        $this->manageTestClient->shouldReceive('findByManageId')
            ->with('manageid')
            ->andReturn($manageDto);

        $entity =$this->copyService->load(
            'dashboardid',
            'manageid',
            $this->service,
            $sourceEnviroment,
            $destinationEvironment
        );

        $redirectUris = $entity->getRedirectUris();
        $playgroundEnabled = $entity->isEnablePlayground();

        $messageFormat = 'Unexpected outcome for the "%s" test in scenario "%s".';

        $this->assertSame($excpectedUris, $redirectUris, sprintf($messageFormat, 'expectedUris', $testName));
        $this->assertSame($expectedEnabledPlayground, $playgroundEnabled, sprintf($messageFormat, 'playgroundEnabled', $testName));
    }

    public function providePlaygroundUrls()
    {
        //$testName, $redirectUris, $sourceEnviroment, $destinationEvironment, $excpectedUris, $expectedEnabledPlayground
        return [
            ['prod-test-enabled', ['url1','url2','url3', self::OIDC_PLAYGROUN_URL_PROD], Entity::ENVIRONMENT_PRODUCTION, Entity::ENVIRONMENT_TEST, ['url1','url2','url3'], true],
            ['prod-prod-enabled', ['url1','url2','url3', self::OIDC_PLAYGROUN_URL_PROD], Entity::ENVIRONMENT_PRODUCTION, Entity::ENVIRONMENT_PRODUCTION, ['url1','url2','url3'], true],
            ['prod-test-disabled', ['url1','url2','url3'], Entity::ENVIRONMENT_PRODUCTION, Entity::ENVIRONMENT_TEST, ['url1','url2','url3'], false],
            ['prod-prod-disabled', ['url1','url2','url3'], Entity::ENVIRONMENT_PRODUCTION, Entity::ENVIRONMENT_PRODUCTION, ['url1','url2','url3'], false],
            ['test-test-enabled', ['url1','url2','url3', self::OIDC_PLAYGROUN_URL_TEST], Entity::ENVIRONMENT_TEST, Entity::ENVIRONMENT_TEST, ['url1','url2','url3'], true],
            ['test-prod-enabled', ['url1','url2','url3', self::OIDC_PLAYGROUN_URL_TEST], Entity::ENVIRONMENT_TEST, Entity::ENVIRONMENT_PRODUCTION, ['url1','url2','url3'], true],
            ['test-test-disabled', ['url1','url2','url3'], Entity::ENVIRONMENT_TEST, Entity::ENVIRONMENT_TEST, ['url1','url2','url3'], false],
            ['test-prod-disabled', ['url1','url2','url3'], Entity::ENVIRONMENT_TEST, Entity::ENVIRONMENT_PRODUCTION, ['url1','url2','url3'], false],
        ];
    }
}
