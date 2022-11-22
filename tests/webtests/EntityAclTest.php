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
        $this->registerManageEntity(
            'test',
            'saml20_idp',
            '0c3febd2-3f67-4b8a-b90d-ce56a3b0abb4',
            'OpenConext Engine',
            'https://engine.dev.support.surfconext.nl/authentication/idp/metadata'
        );

        $this->logIn('ROLE_ADMINISTRATOR');
        $this->switchToService('SURFnet');

        $this->entityId = 'a8e7cffd-0409-45c7-a37a-000000000000';
        $this->serviceId = $service->getId();
    }

    public function test_it_renders_the_form()
    {
        $crawler = $this->client->request('GET', "/entity/acl/{$this->serviceId}/{$this->entityId}");
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
}
