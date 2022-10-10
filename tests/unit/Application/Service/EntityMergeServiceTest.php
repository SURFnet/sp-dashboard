<?php

/**
 * Copyright 2020 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Application\Service;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveSamlEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Service\AttributeService;
use Surfnet\ServiceProviderDashboard\Application\Service\EntityMergeService;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Contact;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Repository\AttributeRepository;

class EntityMergeServiceTest extends TestCase
{
    /** @var EntityMergeService */
    private $service;

    protected function setUp()
    {
        $attributeRepository = new AttributeRepository(__DIR__ . '/../../../../app/config/attributes.json');
        $attributeService = new AttributeService($attributeRepository, 'en');
        $this->service = new EntityMergeService($attributeService, 'test', 'prod');
        parent::setUp();
    }

    public function test_can_create_service()
    {
        $service = new EntityMergeService(m::mock(AttributeService::class), 'test', 'prod');
        self::assertInstanceOf(EntityMergeService::class, $service);
    }

    public function test_it_can_merge_saml_save_command_data_into_an_empty_manage_entity()
    {
        $service = m::mock(Service::class);
        $service->shouldReceive('getOrganizationNameEn', 'getOrganizationNameNl', 'getOrganizationDisplayNameEn', 'getOrganizationDisplayNameNl')
            ->andReturn('Organization Name');
        $manageEntity = $this->service->mergeEntityCommand($this->buildSamlCommand($service), null);

        self::assertNull($manageEntity->getId());
        self::assertFalse($manageEntity->isManageEntity());
        self::assertEquals('https://www.example.com', $manageEntity->getMetaData()->getEntityId());
        self::assertEquals('certdata', $manageEntity->getMetaData()->getCertData());
        self::assertEquals('https://www.example.com/eula', $manageEntity->getMetaData()->getCoin()->getEula());
        self::assertEquals($service, $manageEntity->getService());
        self::assertEquals('Motivation', $manageEntity->getAttributes()->findByUrn('urn:mace:dir:attribute-def:uid')->getMotivation());
        self::assertEquals('John', $manageEntity->getMetaData()->getContacts()->findTechnicalContact()->getGivenName());
        self::assertEquals('Doe', $manageEntity->getMetaData()->getContacts()->findTechnicalContact()->getSurName());
        self::assertEquals('j.doe@example.com', $manageEntity->getMetaData()->getContacts()->findTechnicalContact()->getEmail());
        self::assertEmpty($manageEntity->getMetaData()->getContacts()->findTechnicalContact()->getPhone());
    }

    public function test_it_can_merge_saml_save_command_data_into_a_manage_entity()
    {
        $manageEntity = $this->buildManageEntity();
        $service = m::mock(Service::class);
        $service->shouldReceive('getOrganizationNameEn', 'getOrganizationNameNl', 'getOrganizationDisplayNameEn', 'getOrganizationDisplayNameNl')
            ->andReturn('Organization Name');
        $manageEntity->setService($service);
        // The point of this tests is not to verify all data was correctly merged (see seperate unit tests for that)
        self::assertEquals('https://monitorstands.example.com', $manageEntity->getMetaData()->getEntityId());
        self::assertEquals('SURFconext', $manageEntity->getMetaData()->getContacts()->findAdministrativeContact()->getGivenName());
        $mergedManageEntity = $this->service->mergeEntityCommand(
            $this->buildSamlCommand($service),
            $manageEntity
        );
        // Verify merging was performed by randomly test one value that should have been updated. And one that should have been nulled
        self::assertEquals('https://www.example.com', $mergedManageEntity->getMetaData()->getEntityId());
        self::assertNull($manageEntity->getMetaData()->getContacts()->findAdministrativeContact());
    }

    private function buildManageEntity()
    {
        return ManageEntity::fromApiResponse(json_decode(file_get_contents(__DIR__ . '/fixture/saml20_sp_response.json'), true));
    }

    private function buildSamlCommand(Service $service): SaveSamlEntityCommand
    {
        $command = new SaveSamlEntityCommand();
        $command->setEntityId('https://www.example.com');
        $command->setCertificate('certdata');
        $command->setApplicationUrl('https://www.example.com');
        $command->setEulaUrl('https://www.example.com/eula');
        $attr = new Attribute();
        $attr->setMotivation('Motivation');
        $attr->setRequested(true);
        $command->setAttribute('uidAttribute', $attr);
        $contact = new Contact();
        $contact->setFirstName('John');
        $contact->setLastName('Doe');
        $contact->setEmail('j.doe@example.com');
        $command->setTechnicalContact($contact);
        $command->setService($service);
        return $command;
    }
}
