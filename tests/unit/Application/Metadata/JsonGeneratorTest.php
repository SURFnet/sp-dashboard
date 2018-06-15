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
use Surfnet\ServiceProviderDashboard\Application\Metadata\JsonGenerator\ArpGenerator;
use Surfnet\ServiceProviderDashboard\Application\Metadata\JsonGenerator\PrivacyQuestionsMetadataGenerator;
use Surfnet\ServiceProviderDashboard\Application\Metadata\JsonGenerator\SpDashboardMetadataGenerator;
use Surfnet\ServiceProviderDashboard\Application\Metadata\JsonGenerator;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Contact;

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

    public function test_it_can_build_entity_metadata_for_new_entities()
    {
        $generator = new JsonGenerator(
            $this->arpMetadataGenerator,
            $this->privacyQuestionsMetadataGenerator,
            $this->spDashboardMetadataGenerator
        );

        $metadata = $generator->generateForNewEntity(
            $this->createEntity()
        );

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
        $this->assertEquals(Entity::BINDING_HTTP_POST, $fields['AssertionConsumerService:0:Binding']);
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

    public function test_it_can_build_metadata_for_existing_entities()
    {
        $generator = new JsonGenerator(
            $this->arpMetadataGenerator,
            $this->privacyQuestionsMetadataGenerator,
            $this->spDashboardMetadataGenerator
        );

        $metadata = $generator->generateForExistingEntity(
            $this->createEntity()
        );

        $this->assertArrayNotHasKey('active', $metadata);
        $this->assertArrayNotHasKey('allowedall', $metadata);
        $this->assertArrayNotHasKey('allowedEntities', $metadata);
        $this->assertArrayNotHasKey('state', $metadata);
        $this->assertArrayNotHasKey('type', $metadata);

        $this->assertEquals('http://entityid', $metadata['entityid']);
        $this->assertEquals('http://metadata', $metadata['metadataurl']);
        $this->assertEquals('revisionnote', $metadata['revisionnote']);
        $this->assertEquals(['arp' => 'arp'], $metadata['arp']);

        $this->assertEquals('http://www.w3.org/2001/04/xmldsig-more#rsa-sha256', $metadata['metaDataFields.coin:signature_method']);
        $this->assertEquals('privacy', $metadata['metaDataFields.privacy']);
        $this->assertEquals('sp', $metadata['metaDataFields.sp']);
        $this->assertEquals('http://acs', $metadata['metaDataFields.AssertionConsumerService:0:Location']);
        $this->assertEquals(Entity::BINDING_HTTP_POST, $metadata['metaDataFields.AssertionConsumerService:0:Binding']);
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

    /**
     * @return Entity
     */
    private function createEntity()
    {
        $entity = m::mock(Entity::class)->makePartial();
        $entity->setMetadataUrl('http://metadata');
        $entity->setEntityId('http://entityid');
        $entity->setComments('revisionnote');
        $entity->setAcsLocation('http://acs');
        $entity->setNameEn('name en');
        $entity->setNameNl('name nl');
        $entity->setNameIdFormat('nameidformat');
        $entity->setDescriptionEn('description en');
        $entity->setDescriptionNl('description nl');
        $entity->setCertificate(<<<CERT
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

        return $entity;
    }
}
