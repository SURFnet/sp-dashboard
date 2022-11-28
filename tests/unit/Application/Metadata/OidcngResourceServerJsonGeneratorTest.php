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
use Surfnet\ServiceProviderDashboard\Application\Metadata\OidcngResourceServerJsonGenerator;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Contact;
use Surfnet\ServiceProviderDashboard\Domain\Entity\EntityDiff;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use function file_get_contents;
use function json_decode;

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

        $data = $generator->generateForNewEntity($this->createManageEntity(), 'testaccepted');
        $this->assertEquals(
            [
                'data' => [
                    'type' => 'oauth20-rs',
                    'state' => 'testaccepted',
                    'entityid' => 'entityid',
                    'active' => true,
                    'revisionnote' => 'revisionnote',
                    'metaDataFields' => [
                        'description:en' => 'description en',
                        'description:nl' => 'description nl',
                        'name:en' => 'name en',
                        'name:nl' => 'name nl',
                        'NameIDFormat' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent', // hardcoded
                        'contacts:0:contactType' => 'support',
                        'contacts:0:givenName' => 'givenname',
                        'contacts:0:surName' => 'surname',
                        'contacts:0:emailAddress' => 'emailaddress',
                        'contacts:0:telephoneNumber' => 'telephonenumber',
                        'OrganizationName:en' => 'orgen',
                        'OrganizationName:nl' => 'orgnl',
                        'privacy' => 'privacy',
                        'secret' => 'test',
                        'coin:institution_id' => 'service-institution-id',
                        'coin:institution_guid' => '543b4e5b-76b5-453f-af1e-5648378bb266',
                    ],
                ],
                'type' => 'oauth20_rs',
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

        $diff = m::mock(EntityDiff::class);
        $diff->shouldReceive('getDiff')
            ->andReturn(['metaDataFields.name:en' => 'A new hope']);
        $data = $generator->generateForExistingEntity($this->createManageEntity(), $diff, 'testaccepted');

        $this->assertEquals(
            array(
                'pathUpdates' =>
                    array(
                        'entityid' => 'entityid',
                        'metaDataFields.name:en' => 'A new hope',
                        'state' => 'testaccepted',
                        'revisionnote' => 'revisionnote'
                    ),
                'type' => 'oauth20_rs',
                'id' => 'manageId',
                'active' => true,
            ),
            $data
        );
    }

    public function test_it_builds_an_entity_change_request()
    {
        $generator = new OidcngResourceServerJsonGenerator(
            $this->privacyQuestionsMetadataGenerator,
            $this->spDashboardMetadataGenerator
        );
        $entity = $this->createManageEntity();
        $changedEntity = $this->createChangedManageEntity();
        $diff = $entity->diff($changedEntity);
        $contact = m::mock(Contact::class);
        $contact->shouldReceive('getEmailAddress')->andReturn('j.doe@example.com');
        $data = $generator->generateEntityChangeRequest($entity, $diff, $contact);

        $this->assertIsArray($data);
        $this->assertEquals('manageId', $data['metaDataId']);
        $this->assertEquals('oauth20_rs', $data['type']);
        $this->assertIsArray($data['pathUpdates']);
        $this->assertCount(4, $data['pathUpdates']);
    }

    private function createManageEntity(
        ?bool $idpAllowAll = null,
        ?array $idpWhitelist = null,
        ?string $environment = null
    ): ManageEntity {

        $entity = ManageEntity::fromApiResponse(json_decode(file_get_contents(__DIR__ . '/fixture/oauth20_rs_response.json'), true));
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

    private function createChangedManageEntity()
    {
        $entity = ManageEntity::fromApiResponse(json_decode(file_get_contents(__DIR__ . '/fixture/oauth20_rs_response_changed.json'), true));
        $service = new Service();
        $service->setGuid('543b4e5b-76b5-453f-af1e-5648378bb266');
        $service->setInstitutionId('service-institution-id');
        $entity->setService($service);
        $entity->setComments('revisionnote');
        $entity = m::mock($entity);
        return $entity;
    }
}
