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
use Surfnet\ServiceProviderDashboard\Application\Dto\MetadataConversionDto;
use Surfnet\ServiceProviderDashboard\Application\Metadata\JsonGenerator\ArpGenerator;
use Surfnet\ServiceProviderDashboard\Application\Metadata\JsonGenerator\PrivacyQuestionsMetadataGenerator;
use Surfnet\ServiceProviderDashboard\Application\Metadata\JsonGenerator\SpDashboardMetadataGenerator;
use Surfnet\ServiceProviderDashboard\Application\Metadata\OidcngResourceServerJsonGenerator;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Contact;

class OidcngResourceServerJsonGeneratorTest extends MockeryTestCase
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
        $generator = new OidcngResourceServerJsonGenerator(
            $this->privacyQuestionsMetadataGenerator,
            $this->spDashboardMetadataGenerator
        );

        $data = $generator->generateForNewEntity($this->createOidcngEntity(), 'testaccepted');
        $this->assertEquals(
            [
                'data' => [
                    'type' => 'oidc10-rp',
                    'state' => 'testaccepted',
                    'entityid' => 'entityid',
                    'active' => true,
                    'allowedEntities' => [],
                    'allowedall' => true,
                    'metaDataFields' => [
                        'description:en' => 'description en',
                        'description:nl' => 'description nl',
                        'grants' => [
                            'client_credentials',
                        ],
                        'isResourceServer' => true,
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
                        'scopes' => ['openid'],
                        'privacy' => 'privacy',
                        'secret' => 'test'
                    ],
                ],
                'type' => 'oidc10_rp',
            ],
            $data
        );
    }

    public function test_it_can_build_oidcng_data_for_existing_entities()
    {
        $generator = new OidcngResourceServerJsonGenerator(
            $this->privacyQuestionsMetadataGenerator,
            $this->spDashboardMetadataGenerator
        );

        $data = $generator->generateForExistingEntity($this->createOidcngEntity(), 'testaccepted');

        $this->assertEquals(
            array(
                'pathUpdates' =>
                    array(
                        'entityid' => 'entityid',
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
                        'metaDataFields.scopes' => ['openid'],
                        'metaDataFields.secret' => 'test',
                        'metaDataFields.grants' => [
                            'client_credentials',
                        ],
                        'metaDataFields.isResourceServer' => true,
                        'state' => 'testaccepted',
                        'allowedEntities' => [],
                        'allowedall' => true,
                        'metaDataFields.privacy' => 'privacy'
                    ),
                'type' => 'oidc10_rp',
                'id' => 'manageId',
            ),
            $data
        );
    }

    /**
     * @return MetadataConversionDto
     */
    private function createOidcngEntity()
    {
        /** @var Entity $entity */
        $entity = m::mock(Entity::class)->makePartial();

        $entity->setProtocol('oidcng_rs');

        $entity->setManageId('manageId');
        $entity->setEntityId('http://entityid');
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
        $entity->shouldReceive('isAllowedAll')->andReturn(true);

        return MetadataConversionDto::fromEntity($entity);
    }
}
