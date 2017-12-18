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
use Surfnet\ServiceProviderDashboard\Application\Metadata\JsonGenerator\MotivationMetadataGenerator;
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
     * @var MotivationMetadataGenerator
     */
    private $motivationMetadataGenerator;

    /**
     * @var SpDashboardMetadataGenerator
     */
    private $spDashboardMetadataGenerator;

    public function setUp()
    {
        $this->arpMetadataGenerator = m::mock(ArpGenerator::class);
        $this->privacyQuestionsMetadataGenerator = m::mock(PrivacyQuestionsMetadataGenerator::class);
        $this->motivationMetadataGenerator = m::mock(MotivationMetadataGenerator::class);
        $this->spDashboardMetadataGenerator = m::mock(SpDashboardMetadataGenerator::class);

        $this->arpMetadataGenerator
            ->shouldReceive('build')
            ->andReturn(['arp' => 'arp']);

        $this->privacyQuestionsMetadataGenerator
            ->shouldReceive('build')
            ->andReturn(['privacy' => 'privacy']);

        $this->motivationMetadataGenerator
            ->shouldReceive('build')
            ->andReturn(['motivation' => 'motivation']);

        $this->spDashboardMetadataGenerator
            ->shouldReceive('build')
            ->andReturn(['sp' => 'sp']);
    }

    public function test_it_can_build_entity_metadata()
    {
        /** @var Entity $entity */
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

        $contact = new Contact();
        $contact->setFirstName('givenname');
        $contact->setLastName('surname');
        $contact->setEmail('emailaddress');
        $contact->setPhone('telephonenumber');

        $entity->setSupportContact($contact);

        $generator = new JsonGenerator(
            $this->arpMetadataGenerator,
            $this->privacyQuestionsMetadataGenerator,
            $this->motivationMetadataGenerator,
            $this->spDashboardMetadataGenerator
        );

        $metadata = $generator->generate($entity);

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

        $this->assertEquals('privacy', $fields['privacy']);
        $this->assertEquals('motivation', $fields['motivation']);
        $this->assertEquals('sp', $fields['sp']);
        $this->assertEquals('http://acs', $fields['AssertionConsumerService:0:Location']);
        $this->assertEquals(Entity::BINDING_HTTP_POST, $fields['AssertionConsumerService:0:Binding']);
        $this->assertEquals('nameidformat', $fields['NameIDFormat']);
        $this->assertEquals('name en', $fields['name:en']);
        $this->assertEquals('name nl', $fields['name:nl']);
        $this->assertEquals('description en', $fields['description:en']);
        $this->assertEquals('description nl', $fields['description:nl']);
        $this->assertEquals('certdata', $fields['certData']);

        $this->assertEquals('support', $fields['contacts:0:contactType']);
        $this->assertEquals('givenname', $fields['contacts:0:givenName']);
        $this->assertEquals('surname', $fields['contacts:0:surName']);
        $this->assertEquals('emailaddress', $fields['contacts:0:emailAddress']);
        $this->assertEquals('telephonenumber', $fields['contacts:0:telephoneNumber']);
    }
}
