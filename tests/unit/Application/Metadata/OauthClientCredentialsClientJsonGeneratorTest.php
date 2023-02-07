<?php

/**
 * Copyright 2022 SURFnet B.V.
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
use Surfnet\ServiceProviderDashboard\Application\Metadata\JsonGenerator\PrivacyQuestionsMetadataGenerator;
use Surfnet\ServiceProviderDashboard\Application\Metadata\JsonGenerator\SpDashboardMetadataGenerator;
use Surfnet\ServiceProviderDashboard\Application\Metadata\OauthClientCredentialsClientJsonGenerator;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Contact;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;

class OauthClientCredentialsClientJsonGeneratorTest extends MockeryTestCase
{
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
        $this->privacyQuestionsMetadataGenerator = m::mock(PrivacyQuestionsMetadataGenerator::class);
        $this->spDashboardMetadataGenerator = m::mock(SpDashboardMetadataGenerator::class);

        $this->privacyQuestionsMetadataGenerator
            ->shouldReceive('build')
            ->andReturn(['privacy' => 'privacy']);

        $this->spDashboardMetadataGenerator
            ->shouldReceive('build')
            ->andReturn(['sp' => 'sp']);
    }

    public function test_it_builds_an_entity_change_request()
    {
        $generator = new OauthClientCredentialsClientJsonGenerator(
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
        $this->assertEquals('oidc10_rp', $data['type']);
        $this->assertIsArray($data['pathUpdates']);
        $this->assertCount(2, $data['pathUpdates']);
        $this->assertSame('revisionnote', $data['note']);
    }

    public function test_it_generate_has_revision_note_for_a_new_entity()
    {
        $generator = new OauthClientCredentialsClientJsonGenerator(
            $this->privacyQuestionsMetadataGenerator,
            $this->spDashboardMetadataGenerator
        );
        $entity = $this->createManageEntity();
        $data = $generator->generateForNewEntity($entity, 'prodaccepted');
        $this->assertSame('revisionnote', $data['data']['revisionnote']);
    }

    private function createManageEntity()
    {
        $entity = ManageEntity::fromApiResponse(json_decode(file_get_contents(__DIR__ . '/fixture/oidc10_rp_ccc_response.json'), true));
        $service = new Service();
        $service->setGuid('543b4e5b-76b5-453f-af1e-5648378bb266');
        $service->setInstitutionId('service-institution-id');
        $entity->setService($service);
        $entity->setComments('revisionnote');
        $entity = m::mock($entity);
        return $entity;
    }

    private function createChangedManageEntity()
    {
        $entity = ManageEntity::fromApiResponse(json_decode(file_get_contents(__DIR__ . '/fixture/oidc10_rp_ccc_response_changed.json'), true));
        $service = new Service();
        $service->setGuid('543b4e5b-76b5-453f-af1e-5648378bb266');
        $service->setInstitutionId('service-institution-id');
        $entity->setService($service);
        $entity->setComments('revisionnote');
        $entity = m::mock($entity);
        return $entity;
    }
}
