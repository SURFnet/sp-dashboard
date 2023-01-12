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

namespace Surfnet\ServiceProviderDashboard\Webtests;

class CreateConnectionRequestTest extends WebTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures();
        $this->registerManageEntity(
            'production',
            'saml20_sp',
            '9628d851-abd1-2283-a8f1-a29ba5036174',
            'SURF SP2',
            'https://sp2-surf.com',
            'https://sp2-surf.com/metadata',
            'urn:collab:group:vm.openconext.org:demo:openconext:org:surf.nl'
        );

        $this->manageId = '9729d851-cfdd-4283-a8f1-a29ba5036261';
        $this->logIn('ROLE_ADMINISTRATOR');

        $this->switchToService('Ibuildings B.V.');
    }

    public function test_it_renders_the_form()
    {
        $crawler = $this->client->request('GET', "/entity/create-connection-request/production/9628d851-abd1-2283-a8f1-a29ba5036174/1");
        $form = $crawler->filter('.page-container')
            ->selectButton('Send')
            ->form();

        $buttonField = $form->get('connection_request_container');
        $this->assertEquals(
            '',
            $buttonField['send']->getValue(),
            'connection_request_form_container[send]'
        );
    }
}
