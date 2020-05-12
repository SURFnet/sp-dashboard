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
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Dto\Contact;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Dto\ManageEntity;

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
}
