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

use Surfnet\ServiceProviderDashboard\Application\Service\AttributeService;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Entity\AttributeType;

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
            'production',
            'saml20_sp',
            '9628d851-abd1-2283-a8f1-a29ba5036174',
            'SURF SP2',
            'https://sp2-surf.com',
            'https://sp2-surf.com/metadata',
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

    public function test_it_renders_the_change_request_form()
    {
        $crawler = $this->client->request('GET', "/entity/change-request/test/{$this->manageId}/1");
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());

        $title = $crawler->filter('.page-container h1');
        $this->assertEquals('Change request overview', $title->text());
    }

    public function test_it_renders_the_change_request()
    {
        $crawler = $this->client->request('GET', "/entity/change-request/test/{$this->manageId}/1");
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());

        $date = $crawler->filter('h2')->first();
        // Note the timezone difference here compared to the time found in the fixture.
        // The times in the fixture (in manage) are UTC. We are on Europe/Amsterdam (+2)
        $this->assertEquals('September 21, 2022 15:32', $date->text());
        $note = $crawler->filter('p')->last();
        $this->assertEquals('Optional note describing the reason for this change', $note->text());
        $value = $crawler->filter('td')->first();
        $this->assertEquals('metaDataFields.description:en', $value->text());
        $data = $crawler->filter('td')->last();
        $this->assertEquals('https://nice', trim($data->text()));
    }

    public function test_it_allows_publication_change_requests()
    {
        $crawler = $this->client->request('GET', "/entity/edit/production/9628d851-abd1-2283-a8f1-a29ba5036174/1");
        $form = $crawler
            ->selectButton('Change')
            ->form();
        $this->client->submit($form, $this->buildValidFormData());
        self::assertEquals(302, $this->client->getResponse()->getStatusCode());
        $crawler = $this->client->followRedirect();
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        $message = $crawler->filter('.card p')->first();
        $this->assertEquals(
            'As you where editing a production entity, we have taken your changes under review.',
            trim($message->text())
        );
    }

    private function buildValidFormData()
    {
        /**
         *  The attributes of the form are being built dynamically now, so fetch those attribute names from the
         *  attribute service and built the form data.
         */
        $attributes = $this->getAttributeTypes();

        $result = [
            'dashboard_bundle_entity_type[publishButton]' => '',
            'dashboard_bundle_entity_type[metadata][importUrl]' => 'https://engine.surfconext.nl/authentication/sp/metadata',
            'dashboard_bundle_entity_type[metadata][pastedMetadata]' => '',
            'dashboard_bundle_entity_type[metadata][metadataUrl]' => 'https://sp2-surf.com/metadata',
            'dashboard_bundle_entity_type[metadata][acsLocations]' => [],
            'dashboard_bundle_entity_type[metadata][entityId]' => 'https://sp2-surf.com',
            'dashboard_bundle_entity_type[metadata][certificate]' => file_get_contents(__DIR__ . '/fixtures/publish/valid.cer'),
            'dashboard_bundle_entity_type[metadata][logoUrl]' => 'https://sp2-surf.com/images/logo.png',
            'dashboard_bundle_entity_type[metadata][nameNl]' => 'De Nederlandse naam voor dit entity',
            'dashboard_bundle_entity_type[metadata][descriptionNl]' => 'SURF SP2 Description Dutch',
            'dashboard_bundle_entity_type[metadata][nameEn]' => 'SURF SP2 Name English',
            'dashboard_bundle_entity_type[metadata][descriptionEn]' => 'SURF SP2 Description English',
            'dashboard_bundle_entity_type[metadata][applicationUrl]' => '',
            'dashboard_bundle_entity_type[metadata][eulaUrl]' => '',
            'dashboard_bundle_entity_type[contactInformation][administrativeContact][firstName]' => 'Jane',
            'dashboard_bundle_entity_type[contactInformation][administrativeContact][lastName]' => 'Doe',
            'dashboard_bundle_entity_type[contactInformation][administrativeContact][email]' => 'janedoe@example.com',
            'dashboard_bundle_entity_type[contactInformation][administrativeContact][phone]' => '',
            'dashboard_bundle_entity_type[contactInformation][technicalContact][firstName]' => 'Joe',
            'dashboard_bundle_entity_type[contactInformation][technicalContact][lastName]' => 'Doe',
            'dashboard_bundle_entity_type[contactInformation][technicalContact][email]' => 'JoeDoe@example.com',
            'dashboard_bundle_entity_type[contactInformation][technicalContact][phone]' => '',
            'dashboard_bundle_entity_type[contactInformation][supportContact][firstName]' => 'givenname',
            'dashboard_bundle_entity_type[contactInformation][supportContact][lastName]' => 'surname',
            'dashboard_bundle_entity_type[contactInformation][supportContact][email]' => 'foobar@example.com',
            'dashboard_bundle_entity_type[contactInformation][supportContact][phone]' => 'telephonenumber',
            'dashboard_bundle_entity_type[comments][comments]' => 'I need a new name NL'
        ];

        foreach ($attributes as $attribute) {
            $entry = sprintf('dashboard_bundle_entity_type[attributes][%s][motivation]', $attribute->getName());
            $result[$entry] = 'some data here!';
        }

        $result += [
            'dashboard_bundle_entity_type[status]' => 'published',
            'dashboard_bundle_entity_type[manageId]' => '9628d851-abd1-2283-a8f1-a29ba5036174',
        ];

        return $result;
    }

    /**
     * @return AttributeType[]
     */
    protected function getAttributeTypes(): array
    {
        $service = $this->client->getContainer()->get(AttributeService::class);

        return $service->getAttributeTypeAttributes();
    }
}
