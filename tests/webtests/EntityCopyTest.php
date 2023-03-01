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
            'https://engine.dev.support.surfconext.nl/authentication/sp/consume-assertion',
            $form->get('dashboard_bundle_entity_type[metadata][acsLocations][0]')->getValue()
        );

        $this->assertEquals(
            'https://engine.dev.support.surfconext.nl/authentication/sp/metadata/1430',
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
            'https://engine.dev.support.surfconext.nl/authentication/sp/metadata',
            $form->get('dashboard_bundle_entity_type[metadata][importUrl]')->getValue()
        );

        $this->assertEquals(
            'UID motivation',
            $form->get('dashboard_bundle_entity_type[attributes][uidAttribute][motivation]')->getValue()
        );

        $this->assertEquals(
            "MIIDuDCCAqCgAwIBAgIJAPdqJ9JQKN6vMA0GCSqGSIb3DQEBBQUAMEYxDzANBgNVBAMTBkVuZ2luZTERMA8GA1UECxMIU2VydmljZXMxEzARBgNVBAoTCk9wZW5Db25leHQxCzAJBgNVBAYTAk5MMB4XDTE1MDQwMjE0MDE1NFoXDTI1MDQwMTE0MDE1NFowRjEPMA0GA1UEAxMGRW5naW5lMREwDwYDVQQLEwhTZXJ2aWNlczETMBEGA1UEChMKT3BlbkNvbmV4dDELMAkGA1UEBhMCTkwwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQCeVodghQwFR0pItxGaJ3LXHA+ZLy1w/TMaGDcJaszAZRWRkL/6djwbabR7TB45QN6dfKOFGzobQxG1Oksky3gz4Pki1BSzi/DwsjWCw+Yi40cYpYeg/XM0tvHKVorlsx/7Thm5WuC7rwytujr/lV7f6lavf/ApnLHnOORU2h0ZWctJiestapMaC5mc40msruWWp04axmrYICmTmGhEy7w0qO4/HLKjXtWbJh71GWtJeLzG5Hj04X44wI+D9PUJs9U3SYh9SCFZwq0v+oYeqajiX0JPzB+8aVOPmOOM5WqoT8OCddOM/TlsL/0PcxByGHsgJuWbWMI1PKlK3omR764PAgMBAAGjgagwgaUwHQYDVR0OBBYEFLowmsUCD2CrHU0lich1DMkNppmLMHYGA1UdIwRvMG2AFLowmsUCD2CrHU0lich1DMkNppmLoUqkSDBGMQ8wDQYDVQQDEwZFbmdpbmUxETAPBgNVBAsTCFNlcnZpY2VzMRMwEQYDVQQKEwpPcGVuQ29uZXh0MQswCQYDVQQGEwJOTIIJAPdqJ9JQKN6vMAwGA1UdEwQFMAMBAf8wDQYJKoZIhvcNAQEFBQADggEBAIF9tGG1C9HOSTQJA5qL13y5Ad8G57bJjBfTjp/dw308zwagsdTeFQIgsP4tdQqPMwYmBImcTx6vUNdiwlIol7TBCPGuqQAHD0lgTkChCzWezobIPxjitlkTUZGHqn4Kpq+mFelX9x4BElmxdLj0RQV3c3BhoW0VvJvBkqVKWkZ0HcUTQMlMrQEOq6D32jGh0LPCQN7Ke6ir0Ix5knb7oegND49fbLSxpdo5vSuxQd+Zn6nI1/VLWtWpdeHMKhiw2+/ArR9YM3cY8UwFQOj9Y6wI6gPCGh/q1qv2HnngmnPrNzZik8XucGcf1Wm2zE4UIVYKW31T52mqRVDKRk8F3Eo=",
            $form->get('dashboard_bundle_entity_type[metadata][certificate]')->getValue()
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
