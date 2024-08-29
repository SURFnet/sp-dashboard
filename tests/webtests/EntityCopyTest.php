<?php

/**
 * Copyright 2017 SURFnet B.V.
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

use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;

class EntityCopyTest extends WebTestCase
{
    /**
     * @var Service
     */
    private $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures();
        $this->logIn();

        $this->service = $this->getServiceRepository()->findByName('SURFnet');

        $this->registerManageEntityRaw(
            'test',
            file_get_contents(
                __DIR__ . '/fixtures/entity-copy/remote-entity-info.json'
            )
        );
        $this->registerManageEntityRaw(
            'test',
            file_get_contents(
                __DIR__ . '/fixtures/entity-copy/remote-oidcng-entity-info.json'
            )
        );

        $this->switchToService('SURFnet');
    }

    public function test_copy_does_not_create_new_entity()
    {
        $crawler = self::$pantherClient->request('GET', "/entity/copy/{$this->service->getId()}/d645ddf7-1246-4224-8e14-0d5c494fd9ad");

        self::assertOnPage('Service Provider registration form');

        $this->assertEquals(1, $crawler->selectButton('Publish')->count());

        $form = $crawler->selectButton('Publish')->form();

        $this->assertEquals(
            'https://engine.dev.openconext.local/authentication/sp/consume-assertion',
            $form->get('dashboard_bundle_entity_type[metadata][acsLocations][0]')->getValue()
        );

        $this->assertEquals(
            'https://engine.dev.openconext.local/authentication/sp/metadata/1430',
            $form->get('dashboard_bundle_entity_type[metadata][entityId]')->getValue()
        );

        $this->assertEquals(
            'OpenConext Engine EN',
            $form->get('dashboard_bundle_entity_type[metadata][nameEn]')->getValue()
        );

        $this->assertEquals(
            'OpenConext SSO Proxy EN',
            $form->get('dashboard_bundle_entity_type[metadata][descriptionEn]')->getValue()
        );

        $this->assertEquals(
            'OpenConext Engine',
            $form->get('dashboard_bundle_entity_type[metadata][nameNl]')->getValue()
        );

        $this->assertEquals(
            'OpenConext SSO Proxy',
            $form->get('dashboard_bundle_entity_type[metadata][descriptionNl]')->getValue()
        );

        $this->assertEquals(
            'Support1430',
            $form->get('dashboard_bundle_entity_type[contactInformation][administrativeContact][firstName]')->getValue()
        );

        $this->assertEquals(
            'OpenConext1430',
            $form->get('dashboard_bundle_entity_type[contactInformation][administrativeContact][lastName]')->getValue()
        );

        $this->assertEquals(
            'http://www.example.org/logo.png',
            $form->get('dashboard_bundle_entity_type[metadata][logoUrl]')->getValue()
        );

        $this->assertEquals(
            'https://appurl',
            $form->get('dashboard_bundle_entity_type[metadata][applicationUrl]')->getValue()
        );

        $this->assertEquals(
            'https://eulaurl',
            $form->get('dashboard_bundle_entity_type[metadata][eulaUrl]')->getValue()
        );

        $this->assertEquals(
            'http://www.example.org/metadata',
            $form->get('dashboard_bundle_entity_type[metadata][metadataUrl]')->getValue()
        );

        $this->assertEquals(
            'https://engine.dev.openconext.local/authentication/sp/metadata',
            $form->get('dashboard_bundle_entity_type[metadata][importUrl]')->getValue()
        );

        $this->assertEquals(
            'UID motivation',
            $form->get('dashboard_bundle_entity_type[attributes][uidAttribute][motivation]')->getValue()
        );
    }

    public function test_copy_to_production_for_oidcng_entities_yields_a_protocol_prepend_on_client_id_field()
    {
        $crawler = self::$pantherClient->request('GET', "/entity/copy/1/88888888-0000-9999-1111-777777777777/production");
        // Assert that the newly created entity has the updated client id
        $clientId = $crawler->filter('#dashboard_bundle_entity_type_metadata_clientId')->attr('value');
        $this->assertEquals('https://playground.openconext.nl', $clientId);
    }

    public function test_no_save_button_after_import()
    {
        $crawler = self::$pantherClient->request('GET', "/entity/copy/{$this->service->getId()}/d645ddf7-1246-4224-8e14-0d5c494fd9ad");

        $formData = [
            'dashboard_bundle_entity_type[metadata][importUrl]' => 'https://engine.surfconext.nl/authentication/sp/metadata',
        ];

        $form = $crawler
            ->selectButton('Import')
            ->form();

        $crawler = self::$pantherClient->submit($form, $formData);

        $this->assertEquals(1, $crawler->selectButton('Publish')->count());
    }
}
