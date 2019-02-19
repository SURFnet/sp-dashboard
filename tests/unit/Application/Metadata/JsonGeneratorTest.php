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
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\OidcGrantType;

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
            $this->spDashboardMetadataGenerator,
            'http://playground-test',
            'http://playground-prod'
        );

        $metadata = $generator->generateForNewEntity($this->createSamlEntity(), 'testaccepted');
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

    public function test_it_can_build_saml_metadata_for_existing_entities()
    {
        $generator = new JsonGenerator(
            $this->arpMetadataGenerator,
            $this->privacyQuestionsMetadataGenerator,
            $this->spDashboardMetadataGenerator,
            'http://playground-test',
            'http://playground-prod'
        );

        $metadata = $generator->generateForExistingEntity($this->createSamlEntity(), 'testaccepted');
        $metadata = $metadata['pathUpdates'];

        $this->assertArrayNotHasKey('active', $metadata);
        $this->assertArrayNotHasKey('allowedall', $metadata);
        $this->assertArrayNotHasKey('allowedEntities', $metadata);
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



    public function test_it_can_build_oidc_entity_metadata_for_new_entities()
    {
        $generator = new JsonGenerator(
            $this->arpMetadataGenerator,
            $this->privacyQuestionsMetadataGenerator,
            $this->spDashboardMetadataGenerator,
            'http://playground-test',
            'http://playground-prod'
        );

        $metadata = $generator->generateForNewEntity($this->createOidcEntity(), 'testaccepted');
        $metadata = $metadata['data'];

        $this->assertTrue($metadata['active']);
        $this->assertTrue($metadata['allowedall']);
        $this->assertEmpty($metadata['allowedEntities']);

        $this->assertEquals('http://entityid', $metadata['entityid']);
        $this->assertEquals('testaccepted', $metadata['state']);
        $this->assertEquals('revisionnote', $metadata['revisionnote']);
        $this->assertEquals(['arp' => 'arp'], $metadata['arp']);

        $fields = $metadata['metaDataFields'];

        $this->assertEquals('1', $fields['coin:oidc_client']);
        $this->assertEquals('privacy', $fields['privacy']);
        $this->assertEquals('sp', $fields['sp']);
        //$this->assertEquals('http://acs', $fields['AssertionConsumerService:0:Location']);
        $this->assertNotContains('AssertionConsumerService:0:Binding', $fields);
        $this->assertNotContains('NameIDFormat', $fields);
        $this->assertEquals('name en', $fields['name:en']);
        $this->assertEquals('name nl', $fields['name:nl']);
        $this->assertEquals('description en', $fields['description:en']);
        $this->assertEquals('description nl', $fields['description:nl']);
        //$this->assertEquals('certdata', $fields['certData']);

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

        $this->assertEquals([
            'clientId' => 'http@//entityid',
            'clientSecret' => 'test',
            'redirectUris' => ['uri1','uri2','uri3','http://playground-test'],
            'grantType' => 'implicit',
            'scope' => ['openid'],
        ], $metadata['oidcClient']);
    }

    public function test_it_can_build_oidc_metadata_for_existing_entities()
    {
        $generator = new JsonGenerator(
            $this->arpMetadataGenerator,
            $this->privacyQuestionsMetadataGenerator,
            $this->spDashboardMetadataGenerator,
            'http://playground-test',
            'http://playground-prod'
        );

        $metadata = $generator->generateForExistingEntity($this->createOidcEntity(), 'testaccepted');
        $metadata = $metadata['pathUpdates'];

        $this->assertArrayNotHasKey('active', $metadata);
        $this->assertArrayNotHasKey('allowedall', $metadata);
        $this->assertArrayNotHasKey('allowedEntities', $metadata);
        $this->assertArrayNotHasKey('type', $metadata);

        $this->assertEquals('http://entityid', $metadata['entityid']);
        $this->assertEquals('revisionnote', $metadata['revisionnote']);
        $this->assertEquals(['arp' => 'arp'], $metadata['arp']);

        //$this->assertEquals('http://www.w3.org/2001/04/xmldsig-more#rsa-sha256', $metadata['metaDataFields.coin:signature_method']);
        $this->assertEquals('1', $metadata['metaDataFields.coin:oidc_client']);
        $this->assertEquals('privacy', $metadata['metaDataFields.privacy']);
        $this->assertEquals('sp', $metadata['metaDataFields.sp']);
        //$this->assertEquals('http://acs', $metadata['metaDataFields.AssertionConsumerService:0:Location']);
        $this->assertNotContains('metaDataFields.AssertionConsumerService:0:Binding', $metadata);
        $this->assertNotContains('metaDataFields.NameIDFormat', $metadata);
        $this->assertEquals('name en', $metadata['metaDataFields.name:en']);
        $this->assertEquals('name nl', $metadata['metaDataFields.name:nl']);
        $this->assertEquals('description en', $metadata['metaDataFields.description:en']);
        $this->assertEquals('description nl', $metadata['metaDataFields.description:nl']);
        //$this->assertEquals('certdata', $metadata['metaDataFields.certData']);

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

        $this->assertNotContains('oidcClient', $metadata);
    }

    public function test_it_can_build_saml_entity_data_for_new_entities()
    {
        $generator = new JsonGenerator(
            $this->arpMetadataGenerator,
            $this->privacyQuestionsMetadataGenerator,
            $this->spDashboardMetadataGenerator,
            'http://playground-test',
            'http://playground-prod'
        );

        $data = $generator->generateForNewEntity($this->createSamlEntity(), 'prodaccepted');

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
            $this->spDashboardMetadataGenerator,
            'http://playground-test',
            'http://playground-prod'
        );

        $data = $generator->generateForExistingEntity($this->createSamlEntity(), 'testaccepted');

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
                ),
            'type' => 'saml20_sp',
            'id' => 'manageId',
        ), $data);
    }



    public function test_it_can_build_oidc_entity_data_for_new_entities()
    {
        $generator = new JsonGenerator(
            $this->arpMetadataGenerator,
            $this->privacyQuestionsMetadataGenerator,
            $this->spDashboardMetadataGenerator,
            'http://playground-test',
            'http://playground-prod'
        );

        $data = $generator->generateForNewEntity($this->createOidcEntity(), 'testaccepted');

        $this->assertEquals(array (
            'data' =>
                array (
                    'arp' =>
                        array (
                            'arp' => 'arp',
                        ),
                    'type' => 'saml20-sp',
                    'state' => 'testaccepted',
                    'entityid' => 'http://entityid',
                    'active' => true,
                    'allowedEntities' =>
                        array (
                        ),
                    'allowedall' => true,
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
                            'coin:oidc_client' => '1',
                        ),
                    'oidcClient' =>
                        array (
                            'clientId' => 'http@//entityid',
                            'clientSecret' => 'test',
                            'redirectUris' =>
                                array (
                                    'uri1',
                                    'uri2',
                                    'uri3',
                                    'http://playground-test',
                                ),
                            'grantType' => 'implicit',
                            'scope' =>
                                array (
                                    0 => 'openid',
                                ),
                        ),
                    'revisionnote' => 'revisionnote',
                ),
            'type' => 'saml20_sp',
        ), $data);
    }

    public function test_it_can_build_oidc_data_for_existing_entities()
    {
        $generator = new JsonGenerator(
            $this->arpMetadataGenerator,
            $this->privacyQuestionsMetadataGenerator,
            $this->spDashboardMetadataGenerator,
            'http://playground-test',
            'http://playground-prod'
        );

        $data = $generator->generateForExistingEntity($this->createOidcEntity(), 'testaccepted');

        $this->assertEquals(array (
            'pathUpdates' =>
                array (
                    'arp' =>
                        array (
                            'arp' => 'arp',
                        ),
                    'entityid' => 'http://entityid',
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
                    'metaDataFields.coin:oidc_client' => '1',
                    'revisionnote' => 'revisionnote',
                    'state' => 'testaccepted',
                ),
            'type' => 'saml20_sp',
            'id' => 'manageId',
            'externalReferenceData' =>
                array (
                    'oidcClient' =>
                        array (
                            'clientId' => 'http@//entityid',
                            'clientSecret' => 'test',
                            'redirectUris' =>
                                array (
                                    0 => 'uri1',
                                    1 => 'uri2',
                                    2 => 'uri3',
                                    3 => 'http://playground-test',
                                ),
                            'grantType' => 'implicit',
                            'scope' =>
                                array (
                                    0 => 'openid',
                                ),
                        ),
                ),
        ), $data);
    }


        /**
     * @return Entity
     */
    private function createSamlEntity()
    {
        $entity = m::mock(Entity::class)->makePartial();

        $entity->setProtocol('saml20');

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



    /**
     * @return Entity
     */
    private function createOidcEntity()
    {
        $entity = m::mock(Entity::class)->makePartial();
        //$entity->setMetadataUrl('http://metadata');
        //$entity->setEntityId('http://entityid');
        //$entity->setAcsLocation('http://acs');

        $entity->setProtocol('oidc');
        $entity->setGrantType(new OidcGrantType(OidcGrantType::GRANT_TYPE_AUTHORIZATION_CODE));

        $entity->setManageId('manageId');
        $entity->setEntityId('http://entityid');
        $entity->setComments('revisionnote');
        $entity->setNameEn('name en');
        $entity->setNameNl('name nl');
        $entity->setNameIdFormat('nameidformat');
        $entity->setDescriptionEn('description en');
        $entity->setDescriptionNl('description nl');

        $entity->setOrganizationNameEn('orgen');
        $entity->setOrganizationDisplayNameEn('orgdisen');
        $entity->setOrganizationUrlEn('http://orgen');
        $entity->setOrganizationNameNl('orgnl');
        $entity->setOrganizationDisplayNameNl('orgdisnl');
        $entity->setOrganizationUrlNl('http://orgnl');

        $entity->setClientSecret('test');
        $entity->setRedirectUris([0 => 'uri1', 2 => 'uri2', 8 => 'uri3']);
        $entity->setGrantType(new OidcGrantType('implicit'));
        $entity->setEnablePlayground(true);

        $contact = new Contact();
        $contact->setFirstName('givenname');
        $contact->setLastName('surname');
        $contact->setEmail('emailaddress');
        $contact->setPhone('telephonenumber');

        $entity->setSupportContact($contact);

        return $entity;
    }
}
