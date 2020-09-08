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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Application\Factory;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Surfnet\ServiceProviderDashboard\Application\Metadata\JsonGenerator;
use Surfnet\ServiceProviderDashboard\Application\Metadata\JsonGenerator\ArpGenerator;
use Surfnet\ServiceProviderDashboard\Application\Metadata\JsonGenerator\PrivacyQuestionsMetadataGenerator;
use Surfnet\ServiceProviderDashboard\Application\Metadata\JsonGenerator\SpDashboardMetadataGenerator;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;

class JsonGeneratorTest extends MockeryTestCase
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

        $this->arpMetadataGenerator
            ->shouldReceive('build')
            ->andReturn(['arp' => 'arp']);

        $this->privacyQuestionsMetadataGenerator
            ->shouldReceive('build')
            ->andReturn(['privacy' => 'privacy']);

        $this->spDashboardMetadataGenerator
            ->shouldReceive('build')
            ->andReturn(['sp' => 'sp']);
    }

    public function test_it_can_build_saml_entity_metadata_for_new_entities()
    {
        $generator = new JsonGenerator(
            $this->arpMetadataGenerator,
            $this->privacyQuestionsMetadataGenerator,
            $this->spDashboardMetadataGenerator
        );

        $metadata = $generator->generateForNewEntity($this->createManageEntity(), 'testaccepted');
        $metadata = $metadata['data'];

        $this->assertEquals('saml20-sp', $metadata['type']);

        $this->assertTrue($metadata['active']);
        $this->assertTrue($metadata['allowedall']);
        $this->assertEmpty($metadata['allowedEntities']);

        $this->assertEquals('http://entityid', $metadata['entityid']);
        $this->assertEquals('http://metadata', $metadata['metadataurl']);
        $this->assertEquals('testaccepted', $metadata['state']);
        $this->assertEquals('saml20-sp', $metadata['type']);
        $this->assertEquals('revisionnote', $metadata['revisionnote']);
        $this->assertEquals(['arp' => 'arp'], $metadata['arp']);

        $fields = $metadata['metaDataFields'];

        $this->assertEquals('http://www.w3.org/2001/04/xmldsig-more#rsa-sha256', $fields['coin:signature_method']);
        $this->assertEquals('privacy', $fields['privacy']);
        $this->assertEquals('sp', $fields['sp']);
        $this->assertEquals('http://acs', $fields['AssertionConsumerService:0:Location']);
        $this->assertEquals(Constants::BINDING_HTTP_POST, $fields['AssertionConsumerService:0:Binding']);
        $this->assertEquals('nameidformat', $fields['NameIDFormat']);
        $this->assertEquals('name en', $fields['name:en']);
        $this->assertEquals('name nl', $fields['name:nl']);
        $this->assertEquals('description en', $fields['description:en']);
        $this->assertEquals('description nl', $fields['description:nl']);
        $this->assertEquals('certdata', $fields['certData']);

        $this->assertEquals('orgen', $fields['OrganizationName:en']);
        $this->assertEquals('orgdisen', $fields['OrganizationDisplayName:en']);
        $this->assertEquals('http://orgen', $fields['OrganizationURL:en']);
        $this->assertEquals('orgnl', $fields['OrganizationName:nl']);
        $this->assertEquals('orgdisnl', $fields['OrganizationDisplayName:nl']);
        $this->assertEquals('http://orgnl', $fields['OrganizationURL:nl']);

        $this->assertEquals('support', $fields['contacts:0:contactType']);
        $this->assertEquals('givenname', $fields['contacts:0:givenName']);
        $this->assertEquals('surname', $fields['contacts:0:surName']);
        $this->assertEquals('emailaddress', $fields['contacts:0:emailAddress']);
        $this->assertEquals('telephonenumber', $fields['contacts:0:telephoneNumber']);
    }

    public function test_it_can_build_saml_metadata_for_existing_entities()
    {
        $generator = new JsonGenerator(
            $this->arpMetadataGenerator,
            $this->privacyQuestionsMetadataGenerator,
            $this->spDashboardMetadataGenerator
        );

        $metadata = $generator->generateForExistingEntity($this->createManageEntity(), 'testaccepted');
        $metadata = $metadata['pathUpdates'];

        $this->assertArrayNotHasKey('active', $metadata);
        $this->assertArrayNotHasKey('type', $metadata);

        $this->assertEquals(true, $metadata['allowedall']);
        $this->assertEquals([], $metadata['allowedEntities']);

        $this->assertEquals('http://entityid', $metadata['entityid']);
        $this->assertEquals('http://metadata', $metadata['metadataurl']);
        $this->assertEquals('revisionnote', $metadata['revisionnote']);
        $this->assertEquals(['arp' => 'arp'], $metadata['arp']);

        $this->assertEquals('http://www.w3.org/2001/04/xmldsig-more#rsa-sha256', $metadata['metaDataFields.coin:signature_method']);
        $this->assertEquals('privacy', $metadata['metaDataFields.privacy']);
        $this->assertEquals('sp', $metadata['metaDataFields.sp']);
        $this->assertEquals('http://acs', $metadata['metaDataFields.AssertionConsumerService:0:Location']);
        $this->assertEquals(Constants::BINDING_HTTP_POST, $metadata['metaDataFields.AssertionConsumerService:0:Binding']);
        $this->assertEquals('nameidformat', $metadata['metaDataFields.NameIDFormat']);
        $this->assertEquals('name en', $metadata['metaDataFields.name:en']);
        $this->assertEquals('name nl', $metadata['metaDataFields.name:nl']);
        $this->assertEquals('description en', $metadata['metaDataFields.description:en']);
        $this->assertEquals('description nl', $metadata['metaDataFields.description:nl']);
        $this->assertEquals('certdata', $metadata['metaDataFields.certData']);

        $this->assertEquals('orgen', $metadata['metaDataFields.OrganizationName:en']);
        $this->assertEquals('orgdisen', $metadata['metaDataFields.OrganizationDisplayName:en']);
        $this->assertEquals('http://orgen', $metadata['metaDataFields.OrganizationURL:en']);
        $this->assertEquals('orgnl', $metadata['metaDataFields.OrganizationName:nl']);
        $this->assertEquals('orgdisnl', $metadata['metaDataFields.OrganizationDisplayName:nl']);
        $this->assertEquals('http://orgnl', $metadata['metaDataFields.OrganizationURL:nl']);

        $this->assertEquals('support', $metadata['metaDataFields.contacts:0:contactType']);
        $this->assertEquals('givenname', $metadata['metaDataFields.contacts:0:givenName']);
        $this->assertEquals('surname', $metadata['metaDataFields.contacts:0:surName']);
        $this->assertEquals('emailaddress', $metadata['metaDataFields.contacts:0:emailAddress']);
        $this->assertEquals('telephonenumber', $metadata['metaDataFields.contacts:0:telephoneNumber']);
    }

    public function test_it_can_build_saml_entity_data_for_new_entities()
    {
        $generator = new JsonGenerator(
            $this->arpMetadataGenerator,
            $this->privacyQuestionsMetadataGenerator,
            $this->spDashboardMetadataGenerator
        );

        $data = $generator->generateForNewEntity($this->createManageEntity(), 'prodaccepted');

        $this->assertEquals(array (
            'data' =>
                array (
                    'arp' =>
                        array (
                            'arp' => 'arp',
                        ),
                    'type' => 'saml20-sp',
                    'entityid' => 'http://entityid',
                    'active' => true,
                    'allowedEntities' =>
                        array (
                        ),
                    'allowedall' => true,
                    'state' => 'prodaccepted',
                    'metaDataFields' =>
                        array (
                            'description:en' => 'description en',
                            'description:nl' => 'description nl',
                            'name:en' => 'name en',
                            'name:nl' => 'name nl',
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
                            'sp' => 'sp',
                            'AssertionConsumerService:0:Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                            'AssertionConsumerService:0:Location' => 'http://acs',
                            'NameIDFormat' => 'nameidformat',
                            'coin:signature_method' => 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256',
                            'certData' => 'certdata',
                            'coin:institution_id' => 'service-institution-id',
                            'coin:institution_guid' => '543b4e5b-76b5-453f-af1e-5648378bb266',
                            'coin:exclude_from_push' => '0'
                        ),
                    'metadataurl' => 'http://metadata',
                    'revisionnote' => 'revisionnote',
                ),
            'type' => 'saml20_sp',
        ), $data);
    }

    public function test_it_can_build_saml_data_for_existing_entities()
    {
        $generator = new JsonGenerator(
            $this->arpMetadataGenerator,
            $this->privacyQuestionsMetadataGenerator,
            $this->spDashboardMetadataGenerator
        );

        $data = $generator->generateForExistingEntity($this->createManageEntity(), 'testaccepted');

        $this->assertEquals(array (
            'pathUpdates' =>
                array (
                    'arp' =>
                        array (
                            'arp' => 'arp',
                        ),
                    'entityid' => 'http://entityid',
                    'metadataurl' => 'http://metadata',
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
                    'metaDataFields.sp' => 'sp',
                    'metaDataFields.AssertionConsumerService:0:Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                    'metaDataFields.AssertionConsumerService:0:Location' => 'http://acs',
                    'metaDataFields.NameIDFormat' => 'nameidformat',
                    'metaDataFields.coin:signature_method' => 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256',
                    'metaDataFields.certData' => 'certdata',
                    'state' => 'testaccepted',
                    'revisionnote' => 'revisionnote',
                    'allowedEntities' => [],
                    'allowedall' => true,
                    'metaDataFields.coin:institution_id' => 'service-institution-id',
                    'metaDataFields.coin:institution_guid' => '543b4e5b-76b5-453f-af1e-5648378bb266',
                    'metaDataFields.coin:exclude_from_push' => '0'
                ),
            'type' => 'saml20_sp',
            'id' => 'manageId',
        ), $data);
    }

    public function test_it_can_build_acl_whitelist_for_existing_entities_default_allow_all()
    {
        $generator = new JsonGenerator(
            $this->arpMetadataGenerator,
            $this->privacyQuestionsMetadataGenerator,
            $this->spDashboardMetadataGenerator
        );

        $entity = $this->createManageEntity();

        $data = $generator->generateForExistingEntity($entity, 'testaccepted');

        $this->assertArrayHasKey('allowedall', $data['pathUpdates']);
        $this->assertSame(true, $data['pathUpdates']['allowedall']);
        $this->assertArrayHasKey('allowedEntities', $data['pathUpdates']);
        $this->assertSame([], $data['pathUpdates']['allowedEntities']);
    }

    public function test_it_can_build_acl_whitelist_for_existing_entities_allow_all()
    {
        $generator = new JsonGenerator(
            $this->arpMetadataGenerator,
            $this->privacyQuestionsMetadataGenerator,
            $this->spDashboardMetadataGenerator
        );

        $entity = $this->createManageEntity(true);

        $data = $generator->generateForExistingEntity($entity, 'testaccepted');

        $this->assertArrayHasKey('allowedall', $data['pathUpdates']);
        $this->assertSame(true, $data['pathUpdates']['allowedall']);
        $this->assertArrayHasKey('allowedEntities', $data['pathUpdates']);
        $this->assertSame([], $data['pathUpdates']['allowedEntities']);
    }

    public function test_it_can_build_acl_whitelist_for_existing_entities_none()
    {
        $generator = new JsonGenerator(
            $this->arpMetadataGenerator,
            $this->privacyQuestionsMetadataGenerator,
            $this->spDashboardMetadataGenerator
        );

        $entity = $this->createManageEntity(false);

        $data = $generator->generateForExistingEntity($entity, 'testaccepted');

        $this->assertArrayHasKey('allowedall', $data['pathUpdates']);
        $this->assertSame(false, $data['pathUpdates']['allowedall']);
        $this->assertArrayHasKey('allowedEntities', $data['pathUpdates']);
        $this->assertSame([], $data['pathUpdates']['allowedEntities']);
    }

    public function test_it_can_build_acl_whitelist_for_existing_entities_allow_single()
    {
        $generator = new JsonGenerator(
            $this->arpMetadataGenerator,
            $this->privacyQuestionsMetadataGenerator,
            $this->spDashboardMetadataGenerator
        );

        $idpWhitelist = ['entity-id'];
        $entity = $this->createManageEntity(false, $idpWhitelist);

        $data = $generator->generateForExistingEntity($entity, 'testaccepted');

        $this->assertArrayHasKey('allowedall', $data['pathUpdates']);
        $this->assertSame(false, $data['pathUpdates']['allowedall']);
        $this->assertArrayHasKey('allowedEntities', $data['pathUpdates']);
        $this->assertSame([['name' => 'entity-id']], $data['pathUpdates']['allowedEntities']);
    }

    public function test_exclude_from_push_is_correctly_set()
    {
        $generator = new JsonGenerator(
            $this->arpMetadataGenerator,
            $this->privacyQuestionsMetadataGenerator,
            $this->spDashboardMetadataGenerator
        );

        $entity = $this->createManageEntity(null, null, null, 'production');
        $entity
            ->shouldReceive('isExcludedFromPush')
            ->andReturn(true);

        $entity
            ->shouldReceive('isProduction')
            ->andReturn(true);

        $data = $generator->generateForExistingEntity($entity, 'prodaccepted');

        $this->assertEquals('1', $data['pathUpdates']['metaDataFields.coin:exclude_from_push']);
    }

    public function test_exclude_from_push_is_correctly_set_include()
    {
        $generator = new JsonGenerator(
            $this->arpMetadataGenerator,
            $this->privacyQuestionsMetadataGenerator,
            $this->spDashboardMetadataGenerator
        );
        $entity = $this->createManageEntity(null, null, null, 'production');
        $entity
            ->shouldReceive('isExcludedFromPush')
            ->andReturn(false);

        $entity
            ->shouldReceive('isProduction')
            ->andReturn(true);

        $data = $generator->generateForExistingEntity($entity, 'prodaccepted');

        $this->assertEquals('0', $data['pathUpdates']['metaDataFields.coin:exclude_from_push']);
    }

    public function test_it_can_build_acl_whitelist_for_existing_entities_allow_multiple()
    {
        $generator = new JsonGenerator(
            $this->arpMetadataGenerator,
            $this->privacyQuestionsMetadataGenerator,
            $this->spDashboardMetadataGenerator
        );

        $idpWhitelist = ['entity-id', 'entity-id2'];
        $entity = $this->createManageEntity(false, $idpWhitelist);

        $data = $generator->generateForExistingEntity($entity, 'testaccepted');

        $this->assertArrayHasKey('allowedall', $data['pathUpdates']);
        $this->assertSame(false, $data['pathUpdates']['allowedall']);
        $this->assertArrayHasKey('allowedEntities', $data['pathUpdates']);
        $this->assertSame([['name' => 'entity-id'], ['name' => 'entity-id2'],], $data['pathUpdates']['allowedEntities']);
    }

    public function test_certificate_is_not_required()
    {
        $this->markTestSkipped('Todo enable this test');
        $generator = new JsonGenerator(
            $this->arpMetadataGenerator,
            $this->privacyQuestionsMetadataGenerator,
            $this->spDashboardMetadataGenerator
        );

        $entity = $this->createManageEntity();
        $entity
            ->shouldReceive('getMetaData->getCertData')
            ->andReturn('');

        $data = $generator->generateForNewEntity($entity, 'prodaccepted');

        $this->assertEquals(array (
            'data' =>
                array (
                    'arp' =>
                        array (
                            'arp' => 'arp',
                        ),
                    'type' => 'saml20-sp',
                    'entityid' => 'http://entityid',
                    'active' => true,
                    'allowedEntities' =>
                        array (
                        ),
                    'allowedall' => true,
                    'state' => 'prodaccepted',
                    'metaDataFields' =>
                        array (
                            'description:en' => 'description en',
                            'description:nl' => 'description nl',
                            'name:en' => 'name en',
                            'name:nl' => 'name nl',
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
                            'sp' => 'sp',
                            'AssertionConsumerService:0:Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                            'AssertionConsumerService:0:Location' => 'http://acs',
                            'NameIDFormat' => 'nameidformat',
                            'coin:signature_method' => 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256',
                            'coin:institution_id' => 'service-institution-id',
                            'coin:institution_guid' => '543b4e5b-76b5-453f-af1e-5648378bb266'
                        ),
                    'metadataurl' => 'http://metadata',
                    'revisionnote' => 'revisionnote',
                ),
            'type' => 'saml20_sp',
        ), $data);
    }

    private function createManageEntity(
        ?bool $idpAllowAll = null,
        ?array $idpWhitelist = null,
        ?string $environment = null
    ): ManageEntity {

        $entity = ManageEntity::fromApiResponse(json_decode(file_get_contents(__DIR__ . '/fixture/saml20_sp_response.json'), true));
        $service = new Service();
        $service->setGuid('543b4e5b-76b5-453f-af1e-5648378bb266');
        $service->setInstitutionId('service-institution-id');
        $entity->setService($service);
        $entity->setComments('revisionnote');
        $entity = m::mock($entity);

        if ($idpAllowAll !== null) {
            $entity
                ->shouldReceive('getAllowedIdentityProviders->isAllowAll')
                ->andReturn($idpAllowAll);
        } else {
            $entity
                ->shouldReceive('getAllowedIdentityProviders->isAllowAll')
                ->andReturn(true);
        }

        if ($idpWhitelist !== null) {
            $entity
                ->shouldReceive('getAllowedIdentityProviders->getAllowedIdentityProviders')
                ->andReturn($idpWhitelist);
        } else {
            $entity
                ->shouldReceive('getAllowedIdentityProviders->getAllowedIdentityProviders')
                ->andReturn([]);
        }

        if ($environment !== null) {
            $entity
                ->shouldReceive('getEnvironment')
                ->andReturn($environment);
        }
        return $entity;
    }
}
