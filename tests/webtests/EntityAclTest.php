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

namespace Surfnet\ServiceProviderDashboard\Webtests;

class EntityAclTest extends WebTestCase
{
    private $entityId;
    private $serviceId;

    public function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures();

        $service = $this->getServiceRepository()->findByName('SURFnet');
        $serviceIb = $this->getServiceRepository()->findByName('Ibuildings B.V.');
        $this->registerManageEntity(
            'test',
            'saml20_sp',
            'a8e7cffd-0409-45c7-a37a-000000000000',
            'SP1',
            'https://sp1-entityid.example.com',
            'https://sp1-entityid.example.com/metadata',
            $service->getTeamName()
        );
        $this->registerManageEntity(
            'test',
            'saml20_idp',
            'bfe8f00d-317a-4fbc-9cf8-ad2f3b2af578',
            'OpenConext Mujina IDP',
            'http://mock-idp',
            'https://sp1-entityid.example.com/metadata',
            $service->getTeamName()
        );
        // The IB entity that SURF should not be able to accesss
        $this->registerManageEntity(
            'test',
            'saml20_sp',
            'a8e7cffd-0409-45c7-a37a-000000000001',
            'Ibuildings SP1',
            'https://sp1-ibuildings-entityid.example.com',
            'https://sp1-ibuildings-entityid.example.com/metadata',
            $serviceIb->getTeamName()
        );
        $this->registerManageEntity(
            'test',
            'saml20_idp',
            '0c3febd2-3f67-4b8a-b90d-ce56a3b0abb4',
            'OpenConext Engine',
            'https://engine.dev.openconext.local/authentication/idp/metadata'
        );

        $this->entityId = 'a8e7cffd-0409-45c7-a37a-000000000000';
        $this->serviceId = $service->getId();
    }

    public function test_it_renders_the_form()
    {
        $this->logIn();
        $this->switchToService('SURFnet');

        $crawler = self::$pantherClient->request('GET', "/entity/acl/{$this->serviceId}/{$this->entityId}");
        $form = $crawler->filter('.page-container')
            ->selectButton('Save')
            ->form();
        $selectAllInput = $form->get('acl_entity[selectAll]');
        $this->assertEquals(
            1,
            $selectAllInput->getValue(),
            'Expect the selectAll field to be set'
        );
    }
    public function test_it_not_allowed_on_another_services_acl()
    {
        $service = $this->getServiceRepository()->findByName('SURFnet');
        $serviceIb = $this->getServiceRepository()->findByName('Ibuildings B.V.');
        // Log in as SURFnet
        $this->logIn($service);

        // The SURFnet entity can be displayed on the ACL page
        self::$pantherClient->request('GET', "/entity/acl/{$this->serviceId}/{$this->entityId}");
        self::assertOnPage('Entity Idp access');

        // The SURFnet entity can be displayed on the other Idps ACL page (for connecting test entities)
        self::$pantherClient->request('GET', "/entity/idps/{$this->serviceId}/{$this->entityId}");
        self::assertOnPage('Connect some Idp\'s to your entity');

        // Now go to the page of Ibuildings, which we do not have team membership at
        self::$pantherClient->request('GET', "/entity/acl/{$serviceIb->getId()}/a8e7cffd-0409-45c7-a37a-000000000001");
        self::assertOnPage('You are not allowed to view ACLs of another service');

        // The other Idps acl page shares the authz check the `acl` route also has
        self::$pantherClient->request('GET', "/entity/idps/{$serviceIb->getId()}/a8e7cffd-0409-45c7-a37a-000000000001");
        self::assertOnPage('You are not allowed to view ACLs of another service');
    }
}
