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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Application\Factory;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Surfnet\ServiceProviderDashboard\Application\Metadata\JsonGenerator\ArpGenerator;
use Surfnet\ServiceProviderDashboard\Application\Metadata\JsonGenerator\PrivacyQuestionsMetadataGenerator;
use Surfnet\ServiceProviderDashboard\Application\Metadata\JsonGenerator\SpDashboardMetadataGenerator;
use Surfnet\ServiceProviderDashboard\Application\Metadata\OidcngJsonGenerator;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use function file_get_contents;
use function json_decode;

class OidcngJsonGeneratorTest extends MockeryTestCase
{
    /**
     * @var ArpGenerator
     */
    private $arpMetadataGenerator;

    /**
     * @var PrivacyQuestionsMetadataGenerator
     */
    private $privacyQuestionsMetadataGenerator;

    /**
     * @var SpDashboardMetadataGenerator
     */
    private $spDashboardMetadataGenerator;

    public function setUp()
    {
        $this->arpMetadataGenerator = m::mock(ArpGenerator::class);
        $this->privacyQuestionsMetadataGenerator = m::mock(PrivacyQuestionsMetadataGenerator::class);
        $this->spDashboardMetadataGenerator = m::mock(SpDashboardMetadataGenerator::class);

        $this->privacyQuestionsMetadataGenerator
            ->shouldReceive('build')
            ->andReturn(['privacy' => 'privacy']);

        $this->spDashboardMetadataGenerator
            ->shouldReceive('build')
            ->andReturn([]);
    }

    public function test_it_can_build_oidcng_entity_data_for_new_entities()
    {
        $generator = new OidcngJsonGenerator(
            $this->arpMetadataGenerator,
            $this->privacyQuestionsMetadataGenerator,
            $this->spDashboardMetadataGenerator,
            'http://oidc.test.playground.example.com',
            'http://oidc.prod.playground.example.com'
        );

        $this->arpMetadataGenerator
            ->shouldReceive('build')
            ->andReturn(['arp' => 'arp']);

        $data = $generator->generateForNewEntity($this->createManageEntity(), 'testaccepted');
        $this->assertEquals(
            [
                'data' => [
                    'arp' => ['arp' => 'arp'],
                    'type' => 'oidc10-rp',
                    'state' => 'testaccepted',
                    'entityid' => 'entityid',
                    'active' => true,
                    'allowedEntities' => [],
                    'allowedResourceServers' => [],
                    'allowedall' => true,
                    'metaDataFields' => [
                        'accessTokenValidity' => 3600,
                        'description:en' => 'description en',
                        'description:nl' => 'description nl',
                        'grants' => [
                            'authorization_code',
                            'refresh_token',
                        ],
                        'isPublicClient' => true,
                        'name:en' => 'name en',
                        'name:nl' => 'name nl',
                        'NameIDFormat' => 'nameidformat',
                        'contacts:0:contactType' => 'support',
                        'contacts:0:givenName' => 'givenname',
                        'contacts:0:surName' => 'surname',
                        'contacts:0:emailAddress' => 'emailaddress',
                        'contacts:0:telephoneNumber' => 'telephonenumber',
                        'OrganizationName:en' => 'orgen',
                        'OrganizationName:nl' => 'orgnl',
                        'privacy' => 'privacy',
                        'redirectUrls' => [
                            'uri1',
                            'uri2',
                            'uri3',
                        ],
                        'secret' => 'test',
                        'coin:institution_id' => 'service-institution-id',
                        'coin:institution_guid' => '543b4e5b-76b5-453f-af1e-5648378bb266'
                    ],
                    'revisionnote' => 'revisionnote',
                ],
                'type' => 'oidc10_rp',
            ],
            $data
        );
    }

    public function test_it_can_build_oidcng_data_for_existing_entities()
    {
        $this->arpMetadataGenerator
            ->shouldReceive('build')
            ->andReturn(['arp' => 'arp']);

        $generator = new OidcngJsonGenerator(
            $this->arpMetadataGenerator,
            $this->privacyQuestionsMetadataGenerator,
            $this->spDashboardMetadataGenerator,
            'http://oidc.test.playground.example.com',
            'http://oidc.prod.playground.example.com'
        );

        $data = $generator->generateForExistingEntity($this->createManageEntity(), 'testaccepted');

        $this->assertEquals(
            array(
                'pathUpdates' =>
                    array(
                        'arp' =>
                            array(
                                'arp' => 'arp',
                            ),
                        'entityid' => 'entityid',
                        'metaDataFields.accessTokenValidity' => 3600,
                        'metaDataFields.NameIDFormat' => 'nameidformat',
                        'metaDataFields.description:en' => 'description en',
                        'metaDataFields.description:nl' => 'description nl',
                        'metaDataFields.name:en' => 'name en',
                        'metaDataFields.name:nl' => 'name nl',
                        'metaDataFields.contacts:0:contactType' => 'support',
                        'metaDataFields.contacts:0:givenName' => 'givenname',
                        'metaDataFields.contacts:0:surName' => 'surname',
                        'metaDataFields.contacts:0:emailAddress' => 'emailaddress',
                        'metaDataFields.contacts:0:telephoneNumber' => 'telephonenumber',
                        'metaDataFields.OrganizationName:en' => 'orgen',
                        'metaDataFields.OrganizationName:nl' => 'orgnl',
                        'metaDataFields.privacy' => 'privacy',
                        'metaDataFields.secret' => 'test',
                        'metaDataFields.redirectUrls' => [
                            'uri1',
                            'uri2',
                            'uri3',
                        ],
                        'metaDataFields.grants' => [
                            'authorization_code',
                            'refresh_token',
                        ],
                        'metaDataFields.isPublicClient' => true,
                        'revisionnote' => 'revisionnote',
                        'state' => 'testaccepted',
                        'allowedResourceServers' => [],
                        'metaDataFields.coin:institution_id' => 'service-institution-id',
                        'metaDataFields.coin:institution_guid' => '543b4e5b-76b5-453f-af1e-5648378bb266'
                    ),
                'type' => 'oidc10_rp',
                'id' => 'manageId',
            ),
            $data
        );
    }

    public function test_it_can_build_acl_whitelist_for_existing_entities_default_allow_all()
    {
        $this->arpMetadataGenerator
            ->shouldReceive('build')
            ->andReturn(['arp' => 'arp']);
        $generator = new OidcngJsonGenerator(
            $this->arpMetadataGenerator,
            $this->privacyQuestionsMetadataGenerator,
            $this->spDashboardMetadataGenerator,
            'http://oidc.test.playground.example.com',
            'http://oidc.prod.playground.example.com'
        );

        $data = $generator->generateForExistingEntity($this->createManageEntity(), 'testaccepted', 'ACL');

        $this->assertArrayHasKey('allowedall', $data['pathUpdates']);
        $this->assertSame(true, $data['pathUpdates']['allowedall']);
        $this->assertArrayHasKey('allowedEntities', $data['pathUpdates']);
        $this->assertSame([], $data['pathUpdates']['allowedEntities']);
    }

    public function test_it_can_build_acl_whitelist_for_existing_entities_allow_all()
    {
        $this->arpMetadataGenerator
            ->shouldReceive('build')
            ->andReturn(['arp' => 'arp']);

        $generator = new OidcngJsonGenerator(
            $this->arpMetadataGenerator,
            $this->privacyQuestionsMetadataGenerator,
            $this->spDashboardMetadataGenerator,
            'http://oidc.test.playground.example.com',
            'http://oidc.prod.playground.example.com'
        );

        $entity = $this->createManageEntity(true);

        $data = $generator->generateForExistingEntity($entity, 'testaccepted', 'ACL');

        $this->assertArrayHasKey('allowedall', $data['pathUpdates']);
        $this->assertSame(true, $data['pathUpdates']['allowedall']);
        $this->assertArrayHasKey('allowedEntities', $data['pathUpdates']);
        $this->assertSame([], $data['pathUpdates']['allowedEntities']);
    }

    public function test_it_can_build_acl_whitelist_for_existing_entities_none()
    {
        $this->arpMetadataGenerator
            ->shouldReceive('build')
            ->andReturn(['arp' => 'arp']);

        $generator = new OidcngJsonGenerator(
            $this->arpMetadataGenerator,
            $this->privacyQuestionsMetadataGenerator,
            $this->spDashboardMetadataGenerator,
            'http://oidc.test.playground.example.com',
            'http://oidc.prod.playground.example.com'
        );

        $entity = $this->createManageEntity(false);

        $data = $generator->generateForExistingEntity($entity, 'testaccepted', 'ACL');


        $this->assertArrayHasKey('allowedall', $data['pathUpdates']);
        $this->assertSame(false, $data['pathUpdates']['allowedall']);
        $this->assertArrayHasKey('allowedEntities', $data['pathUpdates']);
        $this->assertSame([], $data['pathUpdates']['allowedEntities']);
    }

    public function test_it_can_build_acl_whitelist_for_existing_entities_allow_single()
    {
        $this->arpMetadataGenerator
            ->shouldReceive('build')
            ->andReturn(['arp' => 'arp']);

        $generator = new OidcngJsonGenerator(
            $this->arpMetadataGenerator,
            $this->privacyQuestionsMetadataGenerator,
            $this->spDashboardMetadataGenerator,
            'http://oidc.test.playground.example.com',
            'http://oidc.prod.playground.example.com'
        );

        $entity = $this->createManageEntity(false, [
            'entity-id',
        ]);

        $data = $generator->generateForExistingEntity($entity, 'testaccepted', 'ACL');

        $this->assertArrayHasKey('allowedall', $data['pathUpdates']);
        $this->assertSame(false, $data['pathUpdates']['allowedall']);
        $this->assertArrayHasKey('allowedEntities', $data['pathUpdates']);
        $this->assertSame([['name' => 'entity-id']], $data['pathUpdates']['allowedEntities']);
    }


    public function test_it_can_build_acl_whitelist_for_existing_entities_allow_multiple()
    {
        $this->arpMetadataGenerator
            ->shouldReceive('build')
            ->andReturn(['arp' => 'arp']);

        $generator = new OidcngJsonGenerator(
            $this->arpMetadataGenerator,
            $this->privacyQuestionsMetadataGenerator,
            $this->spDashboardMetadataGenerator,
            'http://oidc.test.playground.example.com',
            'http://oidc.prod.playground.example.com'
        );

        $entity = $this->createManageEntity(false, [
            'entity-id',
            'entity-id2',
        ]);

        $data = $generator->generateForExistingEntity($entity, 'testaccepted', 'ACL');

        $this->assertArrayHasKey('allowedall', $data['pathUpdates']);
        $this->assertSame(false, $data['pathUpdates']['allowedall']);
        $this->assertArrayHasKey('allowedEntities', $data['pathUpdates']);
        $this->assertSame(
            [['name' => 'entity-id'], ['name' => 'entity-id2'],],
            $data['pathUpdates']['allowedEntities']
        );
    }

    private function createManageEntity(
        ?bool $idpAllowAll = true,
        ?array $idpWhitelist = [],
        ?string $environment = null
    ): ManageEntity {

        $entity = ManageEntity::fromApiResponse(json_decode(file_get_contents(__DIR__ . '/fixture/oidc10_rp_response.json'), true));
        $service = new Service();
        $service->setGuid('543b4e5b-76b5-453f-af1e-5648378bb266');
        $service->setInstitutionId('service-institution-id');
        $entity->setService($service);
        $entity->setComments('revisionnote');
        $entity = m::mock($entity);

        $entity
            ->shouldReceive('getAllowedIdentityProviders->isAllowAll')
            ->andReturn($idpAllowAll);

        $entity
            ->shouldReceive('getAllowedIdentityProviders->getAllowedIdentityProviders')
            ->andReturn($idpWhitelist);

        if ($environment !== null) {
            $entity
                ->shouldReceive('getEnvironment')
                ->andReturn($environment);
        }
        return $entity;
    }
}
