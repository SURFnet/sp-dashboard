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
use Surfnet\ServiceProviderDashboard\Application\Metadata\JsonGenerator;
use Surfnet\ServiceProviderDashboard\Application\Metadata\OidcngJsonGenerator;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\IdentityProvider;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Contact;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\OidcGrantType;

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
            ->with(
                m::on(
                    function (Entity $entity) {
                        $epti = $entity->getEduPersonTargetedIDAttribute();
                        $this->assertTrue($epti->isRequested());
                        $this->assertTrue($epti->hasMotivation());
                        $this->assertEquals('OIDC requires EduPersonTargetedID by default', $epti->getMotivation());

                        return true;
                    }
                )
            )
            ->andReturn(['arp' => 'arp']);

        $data = $generator->generateForNewEntity($this->createOidcngEntity(), 'testaccepted');
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
                        'OrganizationDisplayName:en' => 'orgdisen',
                        'OrganizationURL:en' => 'http://orgen',
                        'OrganizationName:nl' => 'orgnl',
                        'OrganizationDisplayName:nl' => 'orgdisnl',
                        'OrganizationURL:nl' => 'http://orgnl',
                        'privacy' => 'privacy',
                        'redirectUrls' => [
                            'uri1',
                            'uri2',
                            'uri3',
                        ],
                        'secret' => 'test',
                        'scopes' => ['openid'],
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

        $data = $generator->generateForExistingEntity($this->createOidcngEntity(), 'testaccepted');

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
                        'metaDataFields.OrganizationDisplayName:en' => 'orgdisen',
                        'metaDataFields.OrganizationURL:en' => 'http://orgen',
                        'metaDataFields.OrganizationName:nl' => 'orgnl',
                        'metaDataFields.OrganizationDisplayName:nl' => 'orgdisnl',
                        'metaDataFields.OrganizationURL:nl' => 'http://orgnl',
                        'metaDataFields.privacy' => 'privacy',
                        'metaDataFields.scopes' => ['openid'],
                        'metaDataFields.secret' => 'test',
                        'metaDataFields.redirectUrls' => [
                            'uri1',
                            'uri2',
                            'uri3',
                        ],
                        'metaDataFields.grants' => [
                            'authorization_code',
                        ],
                        'metaDataFields.isPublicClient' => true,
                        'revisionnote' => 'revisionnote',
                        'state' => 'testaccepted',
                        'allowedEntities' => [],
                        'allowedResourceServers' => [],
                        'allowedall' => true,
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

        $entity = $this->createOidcngEntity();

        $data = $generator->generateForExistingEntity($entity, 'testaccepted');


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

        $entity = $this->createOidcngEntity();
        $entity->setIdpAllowAll(true);

        $data = $generator->generateForExistingEntity($entity, 'testaccepted');

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

        $entity = $this->createOidcngEntity();
        $entity->setIdpAllowAll(false);

        $data = $generator->generateForExistingEntity($entity, 'testaccepted');


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

        $entity = $this->createOidcngEntity();
        $entity->setIdpAllowAll(false);
        $entity->setIdpWhitelist(
            [
                new IdentityProvider('manage-id', 'entity-id', 'name-nl', 'name-en'),
            ]
        );

        $data = $generator->generateForExistingEntity($entity, 'testaccepted');


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

        $entity = $this->createOidcngEntity();
        $entity->setIdpAllowAll(false);
        $entity->setIdpWhitelist(
            [
                new IdentityProvider('manage-id', 'entity-id', 'name-nl', 'name-en'),
                new IdentityProvider('manage-id2', 'entity-id2', 'name-nl2', 'name-en2'),
            ]
        );

        $data = $generator->generateForExistingEntity($entity, 'testaccepted');


        $this->assertArrayHasKey('allowedall', $data['pathUpdates']);
        $this->assertSame(false, $data['pathUpdates']['allowedall']);
        $this->assertArrayHasKey('allowedEntities', $data['pathUpdates']);
        $this->assertSame(
            [['name' => 'entity-id'], ['name' => 'entity-id2'],],
            $data['pathUpdates']['allowedEntities']
        );
    }


    /**
     * @return Entity
     */
    private function createOidcngEntity()
    {
        /** @var Entity $entity */
        $entity = m::mock(Entity::class)->makePartial();

        $entity->setProtocol('oidcng');

        $entity->setManageId('manageId');
        $entity->setMetadataUrl('http://metadata');
        $entity->setEntityId('http://entityid');
        $entity->setComments('revisionnote');
        $entity->setAcsLocation('http://acs');
        $entity->setNameEn('name en');
        $entity->setNameNl('name nl');
        $entity->setNameIdFormat('nameidformat');
        $entity->setDescriptionEn('description en');
        $entity->setDescriptionNl('description nl');
        $entity->setCertificate(
            <<<CERT
-----BEGIN CERTIFICATE-----
certdata
-----END CERTIFICATE-----
CERT
        );

        $entity->setOrganizationNameEn('orgen');
        $entity->setOrganizationDisplayNameEn('orgdisen');
        $entity->setOrganizationUrlEn('http://orgen');
        $entity->setOrganizationNameNl('orgnl');
        $entity->setOrganizationDisplayNameNl('orgdisnl');
        $entity->setOrganizationUrlNl('http://orgnl');

        $contact = new Contact();
        $contact->setFirstName('givenname');
        $contact->setLastName('surname');
        $contact->setEmail('emailaddress');
        $contact->setPhone('telephonenumber');

        $entity->setSupportContact($contact);

        $entity->setClientSecret('test');
        $entity->setRedirectUris([0 => 'uri1', 2 => 'uri2', 8 => 'uri3']);
        $entity->setGrantType(new OidcGrantType('authorization_code'));
        $entity->setIsPublicClient(true);
        $entity->setAccessTokenValidity(3600);

        $entity->shouldReceive('isAllowedAll')->andReturn(true);
        return $entity;
    }
}
