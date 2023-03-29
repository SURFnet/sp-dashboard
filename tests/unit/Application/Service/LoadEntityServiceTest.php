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

namespace Application\Service;

use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\QueryClient as ManageClient;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Application\Service\LoadEntityService;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Coin;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Repository\AttributesMetadataRepository;

class LoadEntityServiceTest extends MockeryTestCase
{
    /**
     * @var LoadEntityService
     */
    private $copyService;

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

    const OIDC_PLAYGROUND_URL_TEST = 'http://playground-test';
    const OIDC_PLAYGROUND_URL_PROD = 'http://playground-prod';
    const OIDCNG_PLAYGROUND_URL_TEST = 'http://tng-playground-test';
    const OIDCNG_PLAYGROUND_URL_PROD = 'http://tng-playground-prod';

    public function setUp(): void
    {
        parent::setUp();

        $this->manageTestClient = m::mock(ManageClient::class);
        $this->manageProdClient = m::mock(ManageClient::class);
        $this->attributesMetadataRepository = m::mock(AttributesMetadataRepository::class);

        $this->service = new Service();
        $this->service->setTeamName('testteam');

        $this->copyService = new LoadEntityService(
            $this->manageTestClient,
            $this->manageProdClient
        );
    }

    public function test_handler_finds_remote_entity_in_manage()
    {
        $this->manageTestClient->shouldReceive('findByManageId')
            ->with('manageid')
            ->andReturn([]);

        $this->expectExceptionMessage("Could not find entity in manage: manageid");
        $this->expectException(InvalidArgumentException::class);

        $this->copyService->load(
            'manageid',
            $this->service,
            Constants::ENVIRONMENT_TEST,
            Constants::ENVIRONMENT_TEST
        );
    }

    public function test_handler_checks_access_rights_of_user()
    {
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

        $this->expectExceptionMessage("The entity you are about to copy does not belong to the selected team");
        $this->expectException(InvalidArgumentException::class);

        $this->copyService->load(
            'manageid',
            $this->service,
            Constants::ENVIRONMENT_PRODUCTION,
            Constants::ENVIRONMENT_PRODUCTION
        );
    }

    public function test_handler_loads_metadata_onto_new_entity_test()
    {
        $manageDto = ManageEntity::fromApiResponse([
            'id' => '161438a5-50ae-49a6-8ce4-88ea44eef68d',
            'type' => 'saml20_sp',
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

        $entity = $this->copyService->load(
            'manageid',
            $this->service,
            Constants::ENVIRONMENT_TEST,
            Constants::ENVIRONMENT_TEST
        );

        $this->assertInstanceOf(ManageEntity::class, $entity);
    }

    public function test_handler_loads_metadata_onto_new_entity_prod()
    {
        $manageDto = ManageEntity::fromApiResponse([
            'id' => '161438a5-50ae-49a6-8ce4-88ea44eef68d',
            'type' => 'saml20_sp',
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

        $entity = $this->copyService->load(
            'manageid',
            $this->service,
            Constants::ENVIRONMENT_PRODUCTION,
            Constants::ENVIRONMENT_PRODUCTION
        );

        $this->assertInstanceOf(ManageEntity::class, $entity);
    }

    public function test_it_removes_resource_servers_on_copy_to_production()
    {
        $manageDto = ManageEntity::fromApiResponse($this->getOidcNgRPMetadata());
        $this->manageTestClient->shouldReceive('findByManageId')
            ->with('manageid')
            ->andReturn($manageDto);

        $entity = $this->copyService->load(
            'manageid',
            $this->service,
            Constants::ENVIRONMENT_TEST,
            Constants::ENVIRONMENT_PRODUCTION
        );

        $this->assertEmpty($entity->getOidcClient()->getResourceServers());
    }

    public function test_it_keeps_resource_servers_on_copy_to_same_environment()
    {
        $manageDto = ManageEntity::fromApiResponse($this->getOidcNgRPMetadata());

        $this->manageTestClient
            ->shouldReceive('findByManageId')
            ->with('manageid')
            ->andReturn($manageDto);

        $entity = $this->copyService->load(
            'manageid',
            $this->service,
            Constants::ENVIRONMENT_TEST,
            Constants::ENVIRONMENT_TEST
        );

        $this->assertNotEmpty($entity->getOidcClient()->getResourceServers());

        $this->manageProdClient
            ->shouldReceive('findByManageId')
            ->with('manageid')
            ->andReturn($manageDto);

        $entity = $this->copyService->load(
            'manageid',
            $this->service,
            Constants::ENVIRONMENT_PRODUCTION,
            Constants::ENVIRONMENT_PRODUCTION
        );

        $this->assertNotEmpty($entity->getOidcClient()->getResourceServers());
    }

    public function test_it_updates_the_client_id_on_copy_to_production()
    {
        $manageDto = ManageEntity::fromApiResponse($this->getOidcNgRPMetadata());

        $this->manageTestClient->shouldReceive('findByManageId')
            ->with('manageid')
            ->andReturn($manageDto);

        $entity = $this->copyService->load(
            'manageid',
            $this->service,
            Constants::ENVIRONMENT_TEST,
            Constants::ENVIRONMENT_PRODUCTION
        );

        $this->assertEquals(
            'https://playground.openconext.nl',
            $entity->getMetaData()->getEntityId(),
            'The schema should have been put back in place on a copy to production (relying party)'
        );

        // Also verify the Resource Server entity type correctly gets the schema prepend.
        $manageDto = ManageEntity::fromApiResponse($this->getOidcNgRSMetadata());
        $this->manageTestClient
            ->shouldReceive('findByManageId')
            ->with('manageid2')
            ->andReturn($manageDto);
        
        $entity = $this->copyService->load(
            'manageid2',
            $this->service,
            Constants::ENVIRONMENT_TEST,
            Constants::ENVIRONMENT_PRODUCTION
        );

        $this->assertEquals(
            'https://playground.openconext.nl',
            $entity->getMetaData()->getEntityId(),
            'The schema should have been put back in place on a copy to production (resource server)'
        );
    }

    private function getOidcNgRPMetadata()
    {
        return json_decode(
            '
                {
                    "id": "manageid2",
                    "version": 2,
                    "type": "oidc10_rp",
                    "resourceServers": [{
                        "name": "foobar.com"
                    }, {
                        "name": "another-resource-server.com"
                    }],
                    "data": {
                        "type": "oidc10-rp",
                        "entityid": "playground.openconext.nl",
                        "active": true,
                        "state": "prodaccepted",
                        "metaDataFields": {
                            "description:en": "Description in English",
                            "description:nl": "Description in Dutch",
                            "name:en": "SURF Playground",
                            "name:nl": "SURF Speeltuin",
                            "contacts:0:contactType": "technical",
                            "contacts:0:givenName": "Aad",
                            "contacts:0:surName": "Janssen",
                            "contacts:0:emailAddress": "ajanssen@foobar.com",
                            "coin:service_team_id": "testteam",
                            "coin:application_url": "https:\/\/prod.dev.playground.openconext.nl\/playground",
                            "coin:eula": "https:\/\/prod.dev.playground.openconext.nl\/playground\/EULA",
                            "NameIDFormat": "urn:oasis:names:tc:SAML:2.0:nameid-format:transient",
                            "scopes": ["openid"],
                            "secret": "$2a$10$ErGAt73kBcyDI9iPLy0URe5nJrfI84zgSVvyCTGlDyRdXnEzzJS9f",
                            "redirectUrls": 
                                [
                                    "https:\/\/prod.dev.playground.openconext.nl\/redirect1",
                                     "https:\/\/test.dev.playground.openconext.nl"
                                ],
                            "grants": ["authorization_code"],
                            "accessTokenValidity": 4140,
                            "isPublicClient": true,
                            "logo:0:url": 
                                "https:\/\/spdashboard.dev.support.openconext.nl\/images\/openconext-logo.png",
                            "logo:0:width": 322,
                            "logo:0:height": 100,
                            "coin:exclude_from_push": true
                        },
                        "revisionnote": "Revision note 2",
                        "eid": 36,
                        "allowedResourceServers": [{
                            "name": "foobar.com"
                        }, {
                            "name": "another-resource-server.com"
                        }]
                    }
                }
            ',
            true
        );
    }

    private function getOidcNgRSMetadata()
    {
        $metadata = $this->getOidcNgRPMetadata();
        $metadata['data']['metaDataFields']['isResourceServer'] = true;
        return $metadata;
    }
}
