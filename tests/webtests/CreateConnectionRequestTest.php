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

use Facebook\WebDriver\WebDriverBy;

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

        $this->manageId = '9628d851-abd1-2283-a8f1-a29ba5036174';
        $this->logIn('ROLE_ADMINISTRATOR');

        $this->switchToService('SURFnet');
    }

    public function test_it_renders_the_form()
    {
        self::$pantherClient->request('GET', "/entity/create-connection-request/production/9628d851-abd1-2283-a8f1-a29ba5036174/1");
        self::assertOnPage('<h1>Create connection request</h1>');
        self::assertSelectorIsDisabled('#connection_request_container_send');
    }
    public function test_a_connection_request_can_be_created()
    {
        $crawler = self::$pantherClient->request('GET', "/entity/create-connection-request/production/9628d851-abd1-2283-a8f1-a29ba5036174/1");
        $form = $crawler->findElement(WebDriverBy::cssSelector('form[name="connection_request_container"]'));

        self::assertSelectorIsDisabled('#connection_request_container_send');
        self::assertSelectorIsEnabled('#connection_request_container_cancel');

        $this->fillFormField($form, '#connection_request_container_connectionRequests___name___institution', 'Institution 1');
        $this->fillFormField($form, '#connection_request_container_connectionRequests___name___name', 'Jesse');
        $this->fillFormField($form, '#connection_request_container_connectionRequests___name___email', 'jesse-james@gmail.com');
        $this->click($form, '.add_collection_entry');

        self::assertSelectorExists('.collection-list');
        self::assertSelectorIsVisible('.collection-list');

        self::assertSelectorTextContains('.collection-list td:nth-child(1)', 'Institution 1');
        self::assertSelectorIsVisible('.remove_collection_entry');

        self::assertSelectorIsEnabled('#connection_request_container_send');
    }
    public function test_a_connection_request_can_be_validated()
    {
        $crawler = self::$pantherClient->request('GET', "/entity/create-connection-request/production/9628d851-abd1-2283-a8f1-a29ba5036174/1");

        // Scenario 1: no institution
        $form = $crawler->findElement(WebDriverBy::cssSelector('form[name="connection_request_container"]'));
        $this->fillFormField($form, '#connection_request_container_connectionRequests___name___institution', '');
        $this->fillFormField($form, '#connection_request_container_connectionRequests___name___name', 'Jesse');
        $this->fillFormField($form, '#connection_request_container_connectionRequests___name___email', 'jesse-james@gmail.com');
        $this->click($form, '.add_collection_entry');

        self::assertOnPage('This value is required.');
        // Name must be set
        self::assertSelectorAttributeContains(
            '#connection_request_container_connectionRequests___name___institution',
            'class',
            'add-form-input parsley-error'
        );

        // Scenario 2: invalid mail
        $form = $crawler->findElement(WebDriverBy::cssSelector('form[name="connection_request_container"]'));
        $this->fillFormField($form, '#connection_request_container_connectionRequests___name___institution', 'Institution 1');
        $this->fillFormField($form, '#connection_request_container_connectionRequests___name___email', 'jesse-james-at-gmail.com');
        $this->click($form, '.add_collection_entry');

        self::assertOnPage('This value should be a valid email.');

        // Scenario 3: duplicate institution
        $form = $crawler->findElement(WebDriverBy::cssSelector('form[name="connection_request_container"]'));
        $this->fillFormField($form, '#connection_request_container_connectionRequests___name___institution', 'Institution 1');
        $this->fillFormField($form, '#connection_request_container_connectionRequests___name___name', 'Jesse');
        $this->fillFormField($form, '#connection_request_container_connectionRequests___name___email', 'jesse-james@gmail.com');
        $this->click($form, '.add_collection_entry');

        $this->fillFormField($form, '#connection_request_container_connectionRequests___name___institution', 'Institution 1');
        $this->fillFormField($form, '#connection_request_container_connectionRequests___name___name', 'Jane');
        $this->fillFormField($form, '#connection_request_container_connectionRequests___name___email', 'jane-doe@gmail.com');
        $this->click($form, '.add_collection_entry');

        self::assertOnPage('This institution is already requested to be connected.');
    }
}
