<?php

/**
 * Copyright 2018 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Infrastructure\DashboardBundle\Manage\Dto;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Contact;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;

class ManageEntityTest extends MockeryTestCase
{
    public function test_create_dto_from_manage_response()
    {
        $entity = ManageEntity::fromApiResponse(json_decode(file_get_contents(__DIR__ . '/fixture/saml20_sp_response.json'), true));
        $this->assertInstanceOf(ManageEntity::class, $entity);
        // Test some samples
        $this->assertEquals('411f8e0f-87a6-4ea7-8194-66b3e77f97d5', $entity->getId());
        $this->assertEquals('urn:mace:dir:attribute-def:uid', $entity->getAttributes()->findByUrn('urn:mace:dir:attribute-def:uid')->getName());
        $this->assertEquals('Technical Support', $entity->getMetaData()->getContacts()->findTechnicalContact()->getSurName());
        $this->assertEquals('SURFnet BV', $entity->getMetaData()->getOrganization()->getNameEn());
        $this->assertEquals(160, $entity->getMetaData()->getLogo()->getHeight());
        $this->assertTrue($entity->isExcludedFromPush());
    }

    public function test_create_dto_from_manage_response_not_excluded_from_push()
    {
        $entity = ManageEntity::fromApiResponse(json_decode(file_get_contents(__DIR__ . '/fixture/saml20_sp_response_not_excluded_from_push.json'), true));
        $this->assertFalse($entity->isExcludedFromPush());
    }

    public function test_create_dto_with_invalid_contacts_from_manage_response()
    {
        $entity = ManageEntity::fromApiResponse(json_decode(file_get_contents(__DIR__ . '/fixture/saml20_sp_contacts_response.json'), true));
        $this->assertInstanceOf(ManageEntity::class, $entity);

        $contactList = $entity->getMetaData()->getContacts();

        $this->assertSame(null, $contactList->findAdministrativeContact());
        $this->assertSame(null, $contactList->findSupportContact());
        $this->assertInstanceOf(Contact::class, $contactList->findTechnicalContact());

        $contact = $contactList->findTechnicalContact();
        $this->assertSame('technical', $contact->getType());
        $this->assertSame('SURFconext', $contact->getGivenName());
        $this->assertSame('Technical Support', $contact->getSurName());
        $this->assertSame('support@surfconext.nl', $contact->getEmail());
        $this->assertSame('', $contact->getPhone());
    }

    public function test_diff_saml()
    {
        $entity = ManageEntity::fromApiResponse(json_decode(file_get_contents(__DIR__ . '/fixture/saml20_sp_contacts_response_1.json'), true));
        $entity2 = ManageEntity::fromApiResponse(json_decode(file_get_contents(__DIR__ . '/fixture/saml20_sp_contacts_response_2.json'), true));

        $diff = $entity->diff($entity2);
        $diffResults = $diff->getDiff();
        $this->assertCount(4, $diffResults);
        $this->assertEquals('https://monitorstand.example.com', $diffResults['entityid']);
        $this->assertEquals('https://engine.surfconext.com/authentication/metadata', $diffResults['metadataurl']);
        $this->assertEquals('John Doe', $diffResults['metaDataFields.contacts:0:givenName']);
        $this->assertIsArray($diffResults['arp']['attributes']);
        $this->assertCount(2, $diffResults['arp']['attributes']);
        $this->assertEquals('Mumbo-jumbo', $diffResults['arp']['attributes']['urn:mace:dir:attribute-def:displayName']['motivation']);
        $this->assertEquals('sab', $diffResults['arp']['attributes']['urn:mace:dir:attribute-def:uid']['source']);
    }

    public function test_diff_oidc()
    {
        $entity = ManageEntity::fromApiResponse(json_decode(file_get_contents(__DIR__ . '/fixture/oidc_1.json'), true));
        $entity2 = ManageEntity::fromApiResponse(json_decode(file_get_contents(__DIR__ . '/fixture/oidc_2.json'), true));

        $diff = $entity->diff($entity2);
        $diffResults = $diff->getDiff();
        $this->assertCount(5, $diffResults);
        $this->assertEquals('Teams client credentials client for VOOT and FOOT access', $diffResults['metadataFields.name:en']);
        $this->assertIsArray($diffResults['allowedEntities']);
        $this->assertCount(1, $diffResults['allowedEntities']);
        $this->assertIsArray($diffResults['arp']['attributes']);
        $this->assertCount(1, $diffResults['arp']['attributes']);
        $this->assertIsArray($diffResults['metaDataFields.grants']);
        $this->assertCount(1, $diffResults['metaDataFields.grants']);
        $this->assertIsArray($diffResults['metaDataFields.redirectUrls']);
        $this->assertCount(1, $diffResults['metaDataFields.redirectUrls']);
    }
}
