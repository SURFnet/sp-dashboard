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
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\JiraTicketNumber;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\MetaData;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Contact;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use function file_get_contents;
use function json_decode;

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

    public function setUp(): void
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
        $this->privacyQuestionsMetadataGenerator
            ->shouldReceive('withMetadataPrefix');

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

        $contact = m::mock(Contact::class);
        $contact->shouldReceive('getDisplayName')->andReturn('John Doe');
        $contact->shouldReceive('getEmailAddress')->andReturn('jd@example.com');
        $metadata = $generator->generateForNewEntity($this->createManageEntity(), 'testaccepted', $contact);
        $metadata = $metadata['data'];

        $this->assertEquals('saml20-sp', $metadata['type']);

        $this->assertTrue($metadata['active']);
        $this->assertTrue($metadata['allowedall']);
        $this->assertEmpty($metadata['allowedEntities']);

        $this->assertEquals('http://entityid', $metadata['entityid']);
        $this->assertEquals('http://metadata', $metadata['metadataurl']);
        $this->assertEquals('testaccepted', $metadata['state']);
        $this->assertEquals('saml20-sp', $metadata['type']);
        $this->assertMatchesRegularExpression('/Entity Created by user John Doe with email address "jd@example.com"\nVia the SPdashboard on .* \nComment: "revisionnote"/', $metadata['revisionnote']);
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

        $this->assertEquals('orgen', $fields['OrganizationName:en']);
        $this->assertEquals('orgnl', $fields['OrganizationName:nl']);

        $this->assertEquals('support', $fields['contacts:0:contactType']);
        $this->assertEquals('givenname', $fields['contacts:0:givenName']);
        $this->assertEquals('surname', $fields['contacts:0:surName']);
        $this->assertEquals('emailaddress', $fields['contacts:0:emailAddress']);
        $this->assertEquals('telephonenumber', $fields['contacts:0:telephoneNumber']);
        $this->assertEquals(false, $fields['coin:ss:idp_visible_only']);
    }

    public function test_it_can_build_saml_metadata_for_existing_entities()
    {
        $generator = new JsonGenerator(
            $this->arpMetadataGenerator,
            $this->privacyQuestionsMetadataGenerator,
            $this->spDashboardMetadataGenerator
        );

        $entity = $this->createManageEntity();
        $changedEntity = $this->createChangedManageEntity();
        $diff = $entity->diff($changedEntity);
        $contact = m::mock(Contact::class);
        $contact->shouldReceive('getDisplayName')->andReturn('Alec Ann');
        $contact->shouldReceive('getEmailAddress')->andReturn('aa@example.com');
        $metadata = $generator->generateForExistingEntity($entity, $diff, 'testaccepted', $contact);
        $metadata = $metadata['pathUpdates'];

        $this->assertArrayNotHasKey('active', $metadata);
        $this->assertArrayNotHasKey('type', $metadata);

        $this->assertEquals('http://entityid', $metadata['entityid']);
        $this->assertEquals('http://metadata', $metadata['metadataurl']);
        $this->assertStringContainsString('Entity edited by user Alec Ann with email address "aa@example.com"', $metadata['revisionnote']);
        $this->assertStringContainsString('revisionnote', $metadata['revisionnote']);
        $this->assertEquals(['arp' => 'arp'], $metadata['arp']);


        $this->assertEquals('Test Entity EN', $metadata['metaDataFields.name:en']);
        $this->assertEquals('Test Entity NL', $metadata['metaDataFields.name:nl']);
        $this->assertEquals('John Doe', $metadata['metaDataFields.contacts:2:givenName']);
    }

    public function test_it_can_build_saml_entity_data_for_new_entities()
    {
        $generator = new JsonGenerator(
            $this->arpMetadataGenerator,
            $this->privacyQuestionsMetadataGenerator,
            $this->spDashboardMetadataGenerator
        );
        $contact = m::mock(Contact::class);
        $contact->shouldReceive('getDisplayName')->andReturn('John Doe');
        $contact->shouldReceive('getEmailAddress')->andReturn('jd@example.com');
        $data = $generator->generateForNewEntity($this->createManageEntity(), 'prodaccepted', $contact);

        $expected = array (
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
                            'OrganizationName:nl' => 'orgnl',
                            'privacy' => 'privacy',
                            'sp' => 'sp',
                            'AssertionConsumerService:0:Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                            'AssertionConsumerService:0:Location' => 'http://acs',
                            'NameIDFormat' => 'nameidformat',
                            'coin:signature_method' => 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256',
                            'coin:institution_id' => 'service-institution-id',
                            'coin:institution_guid' => '543b4e5b-76b5-453f-af1e-5648378bb266',
                            'coin:exclude_from_push' => '0',
                            'coin:ss:idp_visible_only' => false
                        ),
                    'metadataurl' => 'http://metadata',
                ),
            'type' => 'saml20_sp',
        );

        // Test the revisionNote separately
        $expectedRevisionNote = '/Entity Created by user John Doe with email address "jd@example.com"\nVia the SPdashboard on .* \nComment: "revisionnote"/';
        $actualrevisionNote = $data['data']['revisionnote'];
        unset($data['data']['revisionnote']);
        $this->assertMatchesRegularExpression($expectedRevisionNote, $actualrevisionNote);

        $this->addEmptyAscLocations(1, '', $expected['data']['metaDataFields']);
        $this->assertEquals($expected, $data);
    }

    public function test_it_can_build_saml_data_for_existing_entities()
    {
        $generator = new JsonGenerator(
            $this->arpMetadataGenerator,
            $this->privacyQuestionsMetadataGenerator,
            $this->spDashboardMetadataGenerator
        );

        $entity = $this->createManageEntity();
        $changedEntity = $this->createChangedManageEntity();
        $diff = $entity->diff($changedEntity);

        $contact = m::mock(Contact::class);
        $contact->shouldReceive('getDisplayName')->andReturn('John Doe');
        $contact->shouldReceive('getEmailAddress')->andReturn('jd@example.com');

        $data = $generator->generateForExistingEntity($entity, $diff, 'testaccepted', $contact);

        $expected = array (
            'pathUpdates' =>
                array (
                    'arp' => array ('arp' => 'arp'),
                    'entityid' => 'http://entityid',
                    'metadataurl' => 'http://metadata',
                    'metaDataFields.name:en' => 'Test Entity EN',
                    'metaDataFields.name:nl' => 'Test Entity NL',
                    'state' => 'testaccepted',
                    'metaDataFields.contacts:2:givenName' => 'John Doe',
                    'metaDataFields.coin:exclude_from_push' => '0',
                    'metaDataFields.coin:ss:idp_visible_only' => null,
                    'privacy' => 'privacy',
                ),
            'type' => 'saml20_sp',
            'id' => 'manageId',

        );

        $this->addEmptyAscLocations(1, 'metaDataFields.', $expected['pathUpdates']);

        $expected['pathUpdates']['metaDataFields.AssertionConsumerService:0:Binding'] = Constants::BINDING_HTTP_POST;
        $expected['pathUpdates']['metaDataFields.AssertionConsumerService:0:Location'] = 'http://acs';

        $this->assertStringContainsString('Entity edited by user John Doe with email address "jd@example.com"', $data['pathUpdates']['revisionnote']);
        unset($data['pathUpdates']['revisionnote']);

        $this->assertEquals($expected, $data);
    }

    public function test_it_can_build_acl_whitelist_for_existing_entities_default_allow_all()
    {
        $generator = new JsonGenerator(
            $this->arpMetadataGenerator,
            $this->privacyQuestionsMetadataGenerator,
            $this->spDashboardMetadataGenerator
        );

        $entity = $this->createManageEntity();
        $changedEntity = $this->createChangedManageEntity();
        $diff = $entity->diff($changedEntity);

        $contact = m::mock(Contact::class);
        $contact->shouldReceive('getDisplayName')->andReturn('John Doe');
        $contact->shouldReceive('getEmailAddress')->andReturn('jd@example.com');
        $data = $generator->generateForExistingEntity($entity, $diff, 'testaccepted', $contact, 'ACL');

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
        $changedEntity = $this->createChangedManageEntity();
        $diff = $entity->diff($changedEntity);

        $contact = m::mock(Contact::class);
        $contact->shouldReceive('getDisplayName')->andReturn('John Doe');
        $contact->shouldReceive('getEmailAddress')->andReturn('jd@example.com');
        $data = $generator->generateForExistingEntity($entity, $diff, 'testaccepted', $contact, 'ACL');

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
        $changedEntity = $this->createChangedManageEntity();
        $diff = $entity->diff($changedEntity);

        $contact = m::mock(Contact::class);
        $contact->shouldReceive('getDisplayName')->andReturn('John Doe');
        $contact->shouldReceive('getEmailAddress')->andReturn('jd@example.com');
        $data = $generator->generateForExistingEntity($entity, $diff, 'testaccepted', $contact, 'ACL');

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
        $changedEntity = $this->createChangedManageEntity();
        $diff = $entity->diff($changedEntity);

        $contact = m::mock(Contact::class);
        $contact->shouldReceive('getDisplayName')->andReturn('John Doe');
        $contact->shouldReceive('getEmailAddress')->andReturn('jd@example.com');
        $data = $generator->generateForExistingEntity($entity, $diff, 'testaccepted', $contact, 'ACL');

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
        $changedEntity = $this->createChangedManageEntity();
        $diff = $entity->diff($changedEntity);

        $entity
            ->shouldReceive('isExcludedFromPush')
            ->andReturn(true);

        $entity
            ->shouldReceive('isProduction')
            ->andReturn(true);

        $contact = m::mock(Contact::class);
        $contact->shouldReceive('getDisplayName')->andReturn('John Doe');
        $contact->shouldReceive('getEmailAddress')->andReturn('jd@example.com');
        $data = $generator->generateForExistingEntity($entity, $diff, 'prodaccepted', $contact);

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
        $changedEntity = $this->createChangedManageEntity();
        $diff = $entity->diff($changedEntity);

        $entity
            ->shouldReceive('isExcludedFromPush')
            ->andReturn(false);

        $entity
            ->shouldReceive('isProduction')
            ->andReturn(true);

        $contact = m::mock(Contact::class);
        $contact->shouldReceive('getDisplayName')->andReturn('John Doe');
        $contact->shouldReceive('getEmailAddress')->andReturn('jd@example.com');
        $data = $generator->generateForExistingEntity($entity, $diff, 'prodaccepted', $contact);

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
        $changedEntity = $this->createChangedManageEntity();
        $diff = $entity->diff($changedEntity);

        $contact = m::mock(Contact::class);
        $contact->shouldReceive('getDisplayName')->andReturn('John Doe');
        $contact->shouldReceive('getEmailAddress')->andReturn('jd@example.com');
        $data = $generator->generateForExistingEntity($entity, $diff, 'testaccepted', $contact, 'ACL');

        $this->assertArrayHasKey('allowedall', $data['pathUpdates']);
        $this->assertSame(false, $data['pathUpdates']['allowedall']);
        $this->assertArrayHasKey('allowedEntities', $data['pathUpdates']);
        $this->assertSame([['name' => 'entity-id'], ['name' => 'entity-id2'],], $data['pathUpdates']['allowedEntities']);
    }

    public function test_certificate_is_not_required()
    {
        $generator = new JsonGenerator(
            $this->arpMetadataGenerator,
            $this->privacyQuestionsMetadataGenerator,
            $this->spDashboardMetadataGenerator
        );

        $entity = $this->createManageEntity(true, [], null, true)->shouldIgnoreMissing();

        $entity
            ->shouldReceive('isExcludedFromPush')
            ->andReturn(true);

        $entity
            ->shouldReceive('isProduction')
            ->andReturn(false);

        $contact = m::mock(Contact::class);
        $contact->shouldReceive('getDisplayName')->andReturn('John Doe');
        $contact->shouldReceive('getEmailAddress')->andReturn('jd@example.com');
        $data = $generator->generateForNewEntity($entity, 'prodaccepted', $contact);

        $expected = array (
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
                            'OrganizationName:nl' => 'orgnl',
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
                ),
            'type' => 'saml20_sp',
        );

        // Test the revisionNote separately
        $expectedRevisionNote = '/Entity Created by user John Doe with email address "jd@example.com"\nVia the SPdashboard on .* \nComment: "revisionnote"/';
        $actualrevisionNote = $data['data']['revisionnote'];
        unset($data['data']['revisionnote']);
        $this->assertMatchesRegularExpression($expectedRevisionNote, $actualrevisionNote);

        $this->addEmptyAscLocations(1, '', $expected['data']['metaDataFields']);
        $this->assertEquals($expected, $data);
    }

    public function test_it_builds_an_entity_change_request()
    {
        $generator = new JsonGenerator(
            $this->arpMetadataGenerator,
            $this->privacyQuestionsMetadataGenerator,
            $this->spDashboardMetadataGenerator
        );
        $entity = $this->createManageEntity();
        $changedEntity = $this->createChangedManageEntity();
        $diff = $entity->diff($changedEntity);

        // Do some preliminary diff result assertions
        // The diff should indicate the arp changed. One item was removed,
        // another was changed and the third was added
        $arpAttributes = $diff->getDiff()['arp']['attributes'];
        // New has all three attribute fields
        $this->assertCount(3, $arpAttributes['urn:mace:dir:attribute-def:mail'][0]);
        // Changed attribute only has the changed field
        $this->assertCount(1, $arpAttributes['urn:mace:dir:attribute-def:displayName'][0]);
        // The removed attribute has a null value
        $this->assertNull($arpAttributes['urn:mace:dir:attribute-def:uid']);

        $contact = m::mock(Contact::class);
        $contact->shouldReceive('getEmailAddress')->andReturn('j.doe@example.com');
        $contact->shouldReceive('getDisplayName')->andReturn('A.F.Th. van der Heijden');
        $data = $generator->generateEntityChangeRequest($entity, $diff, $contact, new JiraTicketNumber('CHR-5421'));

        $this->assertIsArray($data);
        $this->assertEquals('manageId', $data['metaDataId']);
        $this->assertEquals('saml20_sp', $data['type']);
        $this->assertIsArray($data['pathUpdates']);
        $this->assertCount(5, $data['pathUpdates']);
        $this->assertStringContainsString('Change request by user A.F.Th. van der Heijden with email address "j.doe@example.com"', $data['note']);
        $this->assertStringContainsString('CHR-5421', $data['note']);
        $this->assertStringContainsString('revisionnote', $data['note']);
    }

    private function createManageEntity(
        ?bool $idpAllowAll = true,
        ?array $idpWhitelist = [],
        ?string $environment = null,
        bool $noCert = false
    ): ManageEntity {

        $entity = ManageEntity::fromApiResponse(json_decode(file_get_contents(__DIR__ . '/fixture/saml20_sp_response.json'), true));
        if ($noCert) {
            $entity = ManageEntity::fromApiResponse(json_decode(file_get_contents(__DIR__ . '/fixture/saml20_sp_response_no_cert.json'), true));
        }
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

    /**
     * Fill empty asc locations to the maximum number
     */
    private function addEmptyAscLocations(int $from, string $prefix, array &$metadata)
    {
        for ($index = $from; $index < MetaData::MAX_ACS_LOCATIONS; $index++) {
            $metadata[sprintf('%sAssertionConsumerService:%d:Binding', $prefix, $index)] = null;
            $metadata[sprintf('%sAssertionConsumerService:%d:Location', $prefix, $index)] = null;
        }
    }

    private function createChangedManageEntity(): ManageEntity
    {
        $entity = ManageEntity::fromApiResponse(json_decode(file_get_contents(__DIR__ . '/fixture/saml20_sp_response_changed.json'), true));
        $service = new Service();
        $service->setGuid('543b4e5b-76b5-453f-af1e-5648378bb266');
        $service->setInstitutionId('service-institution-id');
        $entity->setService($service);
        $entity->setComments('revisionnote');
        $entity = m::mock($entity);
        return $entity;
    }
}
