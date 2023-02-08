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
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Contact;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\Exception\UnknownTypeException;

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
        $this->assertSame('Change request', $entity->getRevisionNote());
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
        $this->assertEquals('John Doe', $diffResults['metaDataFields.contacts:1:givenName']);
        // The ARP is generated in the 'provide everything' fashion the diff will assist us in the JSON generator
        // on whether or not the arp should be part of the payload.
        $this->assertArrayHasKey('arp', $diffResults);
        // Bothe attributes changed
        $this->assertCount(2, $diffResults['arp']['attributes']);
        $this->assertSame('Change request', $entity->getRevisionNote());

    }

    public function test_diff_oidc()
    {
        $entity = ManageEntity::fromApiResponse(json_decode(file_get_contents(__DIR__ . '/fixture/oidc_1.json'), true));
        $entity2 = ManageEntity::fromApiResponse(json_decode(file_get_contents(__DIR__ . '/fixture/oidc_2.json'), true));

        $diff = $entity->diff($entity2);
        $diffResults = $diff->getDiff();
        $this->assertCount(4, $diffResults);
        // Allowed entities are not part of the diff. We generate them in the JSON generator
        $this->assertArrayNotHasKey('allowedEntities', $diffResults);
        // ARP is part of the diff as an indicator of arp changes. We still generate
        // the arp in the JSON generator
        $this->assertArrayHasKey('arp', $diffResults);
        $this->assertArrayHasKey('metaDataFields.name:en', $diffResults);
        $this->assertEquals('Teams client credentials client for VOOT and FOOT access', $diffResults['metaDataFields.name:en']);
        $this->assertIsArray($diffResults['metaDataFields.grants']);
        $this->assertCount(1, $diffResults['metaDataFields.grants']);
        $this->assertIsArray($diffResults['metaDataFields.redirectUrls']);
        // Even though only one item is changed, both items are part of the diff as the redirect URLS are set in a
        // 'provide everything' manner.
        $this->assertCount(2, $diffResults['metaDataFields.redirectUrls']);
        $this->assertSame('Change request', $entity->getRevisionNote());

    }

    public function test_is_status_publication_requested()
    {
        $entity = ManageEntity::fromApiResponse(json_decode(file_get_contents(__DIR__ . '/fixture/saml20_sp_requested.json'), true));
        $entity->updateStatus(Constants::STATE_PUBLICATION_REQUESTED);
        $this->assertEquals(Constants::STATE_PUBLICATION_REQUESTED, $entity->getStatus());
    }

    public function test_is_requested_production_entity_copy()
    {
        $entity = ManageEntity::fromApiResponse(json_decode(file_get_contents(__DIR__ . '/fixture/saml20_sp_requested.json'), true));
        $this->assertTrue($entity->isRequestedProductionEntity(true));
        $this->assertFalse($entity->isRequestedProductionEntity(false));
    }

    public function test_it_has_revision_notes()
    {
        $entity = ManageEntity::fromApiResponse(json_decode(file_get_contents(__DIR__ . '/fixture/saml20_sp_requested.json'), true));
        $entity->setComments('comment on entity');
        $this->assertSame('comment on entity', $entity->getRevisionNote());
        $entity->setComments('another comment on entity');
        $this->assertSame('another comment on entity', $entity->getRevisionNote());
    }

    public function test_it_has_entity_created_revision_notes_for_a_requested_state()
    {
        $entity = ManageEntity::fromApiResponse(json_decode(file_get_contents(__DIR__ . '/fixture/saml20_new_entity.json'), true));
        $this->assertSame('Entity created', $entity->getRevisionNote());
    }

    public function test_it_has_entity_changed_revision_notes_for_a_requested_state()
    {
        $entity = ManageEntity::fromApiResponse(json_decode(file_get_contents(__DIR__ . '/fixture/saml20_changed_entity.json'), true));
        $entity->setStatus(Constants::STATE_PUBLICATION_REQUESTED);
        $this->assertSame('Entity changed', $entity->getRevisionNote());
    }

    public function test_it_has_changed_request_revision_notes_for_a_published_state()
    {
        $entity = ManageEntity::fromApiResponse(json_decode(file_get_contents(__DIR__ . '/fixture/saml20_published.json'), true));
        $this->assertSame('Change request', $entity->getRevisionNote());
    }

    public function test_it_has_entity_created_revision_notes_for_a_test_entity()
    {
        $entity = ManageEntity::fromApiResponse(json_decode(file_get_contents(__DIR__ . '/fixture/saml20_new_test_entity.json'), true));
        $this->assertSame('Entity created', $entity->getRevisionNote());
    }

    public function test_it_has_entity_changed_revision_notes_for_a_test_entity()
    {
        $entity = ManageEntity::fromApiResponse(json_decode(file_get_contents(__DIR__ . '/fixture/saml20_changed_test_entity.json'), true));
        $entity->setStatus(Constants::STATE_PUBLICATION_REQUESTED);
        $this->assertSame('Entity changed', $entity->getRevisionNote());
    }

    public function test_it_has_changed_request_revision_notes_for_a_test_entity()
    {
        $entity = ManageEntity::fromApiResponse(json_decode(file_get_contents(__DIR__ . '/fixture/saml20_published_test_entity.json'), true));
        $this->assertSame('Change request', $entity->getRevisionNote());
    }
}
