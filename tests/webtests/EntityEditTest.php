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

class EntityEditTest extends WebTestCase
{
    private $manageId;

    public function setUp()
    {
        parent::setUp();

        $this->loadFixtures();
        $this->registerManageEntity(
            'test',
            'saml20_sp',
            '9729d851-cfdd-4283-a8f1-a29ba5036261',
            'SP1',
            'https://sp1-entityid.example.com',
            'https://sp1-entityid.example.com/metadata',
            'urn:collab:group:vm.openconext.org:demo:openconext:org:surf.nl'
        );
        $this->registerManageEntity(
            'test',
            'saml20_sp',
            '7398d851-abd1-2283-a8f1-a29ba5036174',
            'Ibuildings SP1',
            'https://sp1-ibuildings.com',
            'https://sp1-ibuildings.com/metadata',
            'urn:collab:org:ibuildings.nl'
        );
        $this->manageId = '9729d851-cfdd-4283-a8f1-a29ba5036261';

        $this->logIn('ROLE_ADMINISTRATOR');

        $this->switchToService('SURFnet');
    }

    public function test_it_renders_the_form()
    {
        $crawler = $this->client->request('GET', "/entity/edit/test/{$this->manageId}/1");

        $form = $crawler->filter('.page-container')
            ->selectButton('Publish')
            ->form();
        $nameEnfield = $form->get('dashboard_bundle_entity_type[metadata][nameEn]');
        $this->assertEquals(
            'SP1 Name English',
            $nameEnfield->getValue(),
            'Expect the NameEN field to be set with value from command'
        );
    }

    public function test_it_rejects_unauthorized_visitors()
    {
        $ibuildings = $this->getServiceRepository()->findByName('Ibuildings B.V.');

        $this->logIn('ROLE_USER', [$ibuildings]);

        $this->client->request('GET', "/entity/edit/test/{$this->manageId}/1");
        $this->assertEquals(500, $this->client->getResponse()->getStatusCode());
    }

    public function test_it_loads_xml_from_url()
    {
        $formData = [
            'dashboard_bundle_entity_type' => [
                'metadata' => [
                    'importUrl' => 'https://engine.surfconext.nl/authentication/sp/metadata',
                ],
            ],
        ];

        $crawler = $this->client->request('GET', "/entity/edit/test/{$this->manageId}/1");

        $form = $crawler
            ->selectButton('Import')
            ->form();

        $this->client->submit($form, $formData);
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function test_it_handles_valid_but_incomplete_metadata()
    {
        $formData = [
            'dashboard_bundle_entity_type' => [
                'metadata' => [
                    'importUrl' => 'https://engine.surfconext.nl/authentication/sp/metadata-valid-incomplete',
                ],
            ],
        ];

        $crawler = $this->client->request('GET', "/entity/edit/test/{$this->manageId}/1");

        $form = $crawler
            ->selectButton('Import')
            ->form();

        $crawler = $this->client->submit($form, $formData);

        $form = $crawler
            ->selectButton('Publish')
            ->form();

        $this->client->submit($form);
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function test_it_loads_xml_from_textarea()
    {
        $xml = file_get_contents(__DIR__ . '/fixtures/metadata/valid_metadata.xml');
        $formData = [
            'dashboard_bundle_entity_type' => [
                'metadata' => [
                    'importUrl' => '',
                    'pastedMetadata' => $xml,
                ],
            ],
        ];

        $crawler = $this->client->request('GET', "/entity/edit/test/{$this->manageId}/1");

        $form = $crawler
            ->selectButton('Import')
            ->form();

        $crawler = $this->client->submit($form, $formData);

        $form = $crawler
            ->selectButton('Publish')
            ->form();

        $this->client->submit($form);
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function test_it_shows_flash_message_on_exception()
    {
        $formData = [
            'dashboard_bundle_entity_type' => [
                'metadata' => [
                    'importUrl' => 'https://this.does.not/exist',
                ],
            ],
        ];

        $crawler = $this->client->request('GET', "/entity/edit/test/{$this->manageId}/1");

        $form = $crawler
            ->selectButton('Import')
            ->form();

        $crawler = $this->client->submit($form, $formData);
        $message = $crawler->filter('.message.error')->first();

        $this->assertEquals(
            'The provided metadata is invalid.',
            trim($message->text()),
            'Expected an error message for this invalid importUrl'
        );
    }

    public function test_it_shows_flash_message_on_parse_exception()
    {
        $xml = file_get_contents(__DIR__ . '/fixtures/metadata/invalid_metadata.xml');
        $formData = [
            'dashboard_bundle_entity_type' => [
                'metadata' => [
                    'importUrl' => '',
                    'pastedMetadata' => $xml,
                ],
            ],
        ];

        $crawler = $this->client->request('GET', "/entity/edit/test/{$this->manageId}/1");

        $form = $crawler
            ->selectButton('Import')
            ->form();

        $crawler = $this->client->submit($form, $formData);
        $message = $crawler->filter('.message.error')->first();

        $this->assertEquals(
            'The provided metadata is invalid.',
            trim($message->text()),
            'Expected an error message for this invalid importUrl'
        );

        $genericMessage = $crawler->filter('.message.preformatted')->eq(0);
        $notAllowedMessage = $crawler->filter('.message.preformatted')->eq(1);
        $missingMessage = $crawler->filter('.message.preformatted')->eq(2);

        $this->assertContains(
            "The metadata XML is invalid considering the associated XSD",
            $genericMessage->text(),
            'Expected an XML parse error.'
        );
        $this->assertContains(
            "EntityDescriptor', attribute 'entityED': The attribute 'entityED' is not allowed.",
            $notAllowedMessage->text(),
            'Expected an XML parse error.'
        );
        $this->assertContains(
            "EntityDescriptor': The attribute 'entityID' is required but missing.",
            $missingMessage->text(),
            'Expected an XML parse error.'
        );
    }
}
