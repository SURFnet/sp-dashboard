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
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\DataFixtures\ORM\WebTestFixtures;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Entity\AttributeType;

class EntityEditTest extends WebTestCase
{
    private $manageId;

    public function setUp(): void
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
            WebTestFixtures::TEAMNAME_SURF
        );
        $this->registerManageEntity(
            'production',
            'saml20_sp',
            '9628d851-abd1-2283-a8f1-a29ba5036174',
            'SURF SP2',
            'https://sp2-surf.com',
            'https://sp2-surf.com/metadata',
            WebTestFixtures::TEAMNAME_SURF
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

        $this->logIn();

        $this->switchToService('SURFnet');
    }

    public function test_it_renders_the_form()
    {
        $crawler = self::$pantherClient->request('GET', "/entity/edit/test/{$this->manageId}/1");

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

    public function test_it_hides_name_id_format_unspecified()
    {
        // 1. When NameIdFormat is not Unspecified, the form field does appear on the form
        $crawler = self::$pantherClient->request('GET', "/entity/edit/test/{$this->manageId}/1");
        $this->assertOnPage(
            'Name id format',
            $crawler
        );
        // 2. But when the nameIdFormat is unspecified, the form field is not displayed on the form
        $this->registerManageEntity(
            'test',
            'saml20_sp',
            '88888888-1111-1111-1111-888888888888',
            'SP1',
            'https://spx-entityid.example.com',
            'https://spx-entityid.example.com/metadata',
            WebTestFixtures::TEAMNAME_SURF,
            '12',
            Constants::NAME_ID_FORMAT_UNSPECIFIED
        );
        $crawler = self::$pantherClient->request('GET', "/entity/edit/test/88888888-1111-1111-1111-888888888888/1");
        $this->assertNotOnPage(
            'Name id format',
            $crawler
        );
    }

    public function test_it_rejects_unauthorized_visitors()
    {
        $ibuildings = $this->getServiceRepository()->findByName('Ibuildings B.V.');
        $this->logOut();
        $this->logIn($ibuildings);

        self::$pantherClient->request('GET', "/entity/edit/test/{$this->manageId}/1");
        self::assertOnPage('HTTP 500');
        self::assertOnPage('User is not granted access to service with ID');
    }

    public function test_it_loads_xml_from_url()
    {
        $formData = [
            'dashboard_bundle_entity_type[metadata][importUrl]' => 'https://engine.surfconext.nl/authentication/sp/metadata'
        ];

        $crawler = self::$pantherClient->request('GET', "/entity/edit/test/{$this->manageId}/1");

        $form = $crawler
            ->selectButton('Import')
            ->form();
        self::$pantherClient->submit($form, $formData);
        self::assertInputValueSame('dashboard_bundle_entity_type[metadata][entityId]', 'https://domain.org/saml/metadata');
        self::assertInputValueSame('dashboard_bundle_entity_type[metadata][logoUrl]', 'https://LOGO.example.com/logo.png');
    }

    public function test_it_handles_valid_but_incomplete_metadata()
    {
        $formData = ['dashboard_bundle_entity_type[metadata][importUrl]' => 'https://engine.surfconext.nl/authentication/sp/metadata-valid-incomplete'];

        $crawler = self::$pantherClient->request('GET', "/entity/edit/test/{$this->manageId}/1");

        $form = $crawler
            ->selectButton('Import')
            ->form();

        $crawler = self::$pantherClient->submit($form, $formData);

        $form = $crawler
            ->selectButton('Publish')
            ->form();

        self::$pantherClient->submit($form);
        self::assertOnPage('Warning! Some entries are missing or incorrect');
    }

    public function test_it_loads_xml_from_textarea()
    {
        $xml = file_get_contents(__DIR__ . '/fixtures/metadata/valid_metadata.xml');
        $formData = [
            'dashboard_bundle_entity_type[metadata][importUrl]' => '',
            'dashboard_bundle_entity_type[metadata][pastedMetadata]' => $xml,
        ];

        $this->testPublicationClient->registerPublishResponse(
            'https://domain.org/saml/metadata',
            '{"id":"f1e394b2-08b1-4882-8b32-43876c15c743"}'
        );

        $crawler = self::$pantherClient->request('GET', "/entity/edit/test/{$this->manageId}/1");

        $form = $crawler
            ->selectButton('Import')
            ->form();

        self::$pantherClient->submit($form, $formData);

        // Fill the form motivations (required when publishing an entity)
        $formElement = self::findBy('form[name="dashboard_bundle_entity_type"]');
        self::fillFormField($formElement, '#dashboard_bundle_entity_type_attributes_emailAddressAttribute_motivation', 'foo');
        self::fillFormField($formElement, '#dashboard_bundle_entity_type_attributes_commonNameAttribute_motivation', 'foo');
        self::fillFormField($formElement, '#dashboard_bundle_entity_type_attributes_organizationAttribute_motivation', 'foo');
        self::fillFormField($formElement, '#dashboard_bundle_entity_type_attributes_personalCodeAttribute_motivation', 'foo');
        // Also check a type of service (as they are mandatory)
        self::findBy('option.decorated:nth-child(4)')->click();
        self::findBy('#dashboard_bundle_entity_type_publishButton')->click();

        $pageTitle = self::$pantherClient->refreshCrawler()->filter('h1')->first()->text();
        self::assertOnPage('Your changes were saved!');
        self::assertEquals('Successfully published the entity to test', $pageTitle);
    }

    public function test_it_shows_flash_message_on_exception()
    {
        $formData = [
            'dashboard_bundle_entity_type[metadata][importUrl]' => 'https://this.does.not/exist'
        ];

        $crawler = self::$pantherClient->request('GET', "/entity/edit/test/{$this->manageId}/1");

        $form = $crawler
            ->selectButton('Import')
            ->form();

        $crawler = self::$pantherClient->submit($form, $formData);
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
            'dashboard_bundle_entity_type[metadata][importUrl]' => '',
            'dashboard_bundle_entity_type[metadata][pastedMetadata]' => $xml,
        ];

        $crawler = self::$pantherClient->request('GET', "/entity/edit/test/{$this->manageId}/1");

        $form = $crawler
            ->selectButton('Import')
            ->form();

        $crawler = self::$pantherClient->submit($form, $formData);
        $message = $crawler->filter('.message.error')->first();

        $this->assertEquals(
            'The provided metadata is invalid.',
            trim($message->text()),
            'Expected an error message for this invalid importUrl'
        );

        $genericMessage = $crawler->filter('.message.preformatted')->eq(0);
        $notAllowedMessage = $crawler->filter('.message.preformatted')->eq(1);
        $missingMessage = $crawler->filter('.message.preformatted')->eq(2);

        $this->assertStringContainsString(
            "The metadata XML is invalid considering the associated XSD",
            $genericMessage->text(),
            'Expected an XML parse error.'
        );
        $this->assertStringContainsString(
            "EntityDescriptor', attribute 'entityED': The attribute 'entityED' is not allowed.",
            $notAllowedMessage->text(),
            'Expected an XML parse error.'
        );
        $this->assertStringContainsString(
            "EntityDescriptor': The attribute 'entityID' is required but missing.",
            $missingMessage->text(),
            'Expected an XML parse error.'
        );
    }

    public function test_it_renders_the_change_request_form()
    {
        $crawler = self::$pantherClient->request('GET', "/entity/change-request/test/{$this->manageId}/1");
        $title = $crawler->filter('.page-container h1');
        $this->assertEquals('Change request overview', $title->text());
    }

    public function test_it_renders_the_change_request()
    {
        $crawler = self::$pantherClient->request('GET', "/entity/change-request/test/{$this->manageId}/1");
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
        $this->prodPublicationClient->registerPublishResponse(
            'https://sp2-surf.com/metadata',
            '{"id":"9628d851-abd1-2283-a8f1-a29ba5036174"}'
        );

        $crawler = self::$pantherClient->request('GET', "/entity/edit/production/9628d851-abd1-2283-a8f1-a29ba5036174/1");
        $form = $crawler
            ->selectButton('Change')
            ->form();
        self::$pantherClient->submit($form, $this->buildValidFormData());
        $this->assertOnPage('As you where editing a production entity, we have taken your changes under review.');
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
            'dashboard_bundle_entity_type[metadata][entityId]' => 'https://sp2-surf.com',
            'dashboard_bundle_entity_type[metadata][nameIdFormat]' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient',
            'dashboard_bundle_entity_type[metadata][certificate]' => file_get_contents(__DIR__ . '/fixtures/publish/valid.cer'),
            'dashboard_bundle_entity_type[metadata][logoUrl]' => 'https://sp2-surf.com/images/logo.png',
            'dashboard_bundle_entity_type[metadata][nameNl]' => 'De Nederlandse naam voor dit entity',
            'dashboard_bundle_entity_type[metadata][descriptionNl]' => 'SURF SP2 Description Dutch',
            'dashboard_bundle_entity_type[metadata][nameEn]' => 'SURF SP2 Name English',
            'dashboard_bundle_entity_type[metadata][descriptionEn]' => 'SURF SP2 Description English',
            'dashboard_bundle_entity_type[metadata][applicationUrl]' => '',
            'dashboard_bundle_entity_type[metadata][eulaUrl]' => '',
            'dashboard_bundle_entity_type[metadata][typeOfService][]' => 'Research',
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
            'dashboard_bundle_entity_type[comments][comments]' => 'I need a new name NL',
        ];

        foreach ($attributes as $attribute) {

            $entry = sprintf('dashboard_bundle_entity_type[attributes][%s][motivation]', $attribute->getName());
            $entryRequested = sprintf('dashboard_bundle_entity_type[attributes][%s][requested]', $attribute->getName());
            $result[$entryRequested] = true;
            $result[$entry] = 'some data here!';
        }

        return $result;
    }

    /**
     * @return AttributeType[]
     */
    protected function getAttributeTypes(): array
    {
        $service = self::getContainer()->get(AttributeService::class);

        return $service->getAttributeTypeAttributes();
    }
}
