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

use Facebook\WebDriver\Exception\NoSuchElementException;
use Surfnet\ServiceProviderDashboard\Application\ViewObject\Attribute;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\DataFixtures\ORM\WebTestFixtures;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;
use Facebook\WebDriver\WebDriverBy;

class EntityCreateSamlTest extends WebTestCase
{

    public function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures();
        $this->logIn();
        $this->switchToService('Ibuildings B.V.');
    }

    public function test_it_renders_the_form()
    {
        $crawler = self::$pantherClient->request('GET', '/entity/create/2/saml20/test');
        $form = $crawler->filter('.page-container')
            ->selectButton('Publish')
            ->form();

        $nameEnfield = $form->get('dashboard_bundle_entity_type[metadata][nameEn]');
        $nameIdFormat = $form->get('dashboard_bundle_entity_type[metadata][nameIdFormat]');

        self::assertEquals(
            '',
            $nameEnfield->getValue(),
            'Expect the NameEN field to be empty'
        );

        self::assertInstanceOf(
            ChoiceFormField::class,
            $nameIdFormat,
            'Expect the NameIdFormat to be a radio group'
        );
    }

    public function test_it_imports_metadata()
    {
        $formData = [
            'dashboard_bundle_entity_type[metadata][importUrl]' =>
                'https://engine.surfconext.nl/authentication/sp/metadata',
        ];

        $crawler = self::$pantherClient->request('GET', '/entity/create/2/saml20/test');

        $form = $crawler
            ->selectButton('Import')
            ->form();

        $crawler = self::$pantherClient->submit($form, $formData);

        $form = $crawler->selectButton('Publish')->form();

        // The imported metadata is loaded in the form (see: /fixtures/metadata/valid_metadata.xml)
        self::assertEquals(
            'DNNL',
            $form->get('dashboard_bundle_entity_type[metadata][nameNl]')->getValue()
        );
        self::assertEquals(
            'DNEN',
            $form->get('dashboard_bundle_entity_type[metadata][nameEn]')->getValue()
        );
    }

    public function test_it_can_cancel_out_of_the_form()
    {
        $crawler = self::$pantherClient->request('GET', '/entity/create/2/saml20/test');
        $form = $crawler
            ->selectButton('Cancel')
            ->form();

        self::$pantherClient->submit($form);
        self::$pantherClient->followRedirects();

        $crawler = self::$pantherClient->getCrawler();
        $pageTitle = $crawler->filter('.service-title')->first()->text();
        $messageTest = $crawler->filter('.no-entities-test')->text();
        $messageProduction = $crawler->filter('.no-entities-production')->text();

        static::assertStringContainsString('Ibuildings B.V. overview', $pageTitle);
        static::assertStringContainsString('No entities found.', $messageTest);
        static::assertStringContainsString('No entities found.', $messageProduction);
    }

    public function test_it_requires_an_acs_location()
    {
        $formData = $this->buildValidFormData();

        $crawler = self::$pantherClient->request('GET', '/entity/create/2/saml20/test');

        $form = $crawler
            ->selectButton('Publish')
            ->form();

        self::$pantherClient->submit($form, $formData);
        self::$pantherClient->followRedirects();

        $errorText = self::$pantherClient->getCrawler()->findElement(
            WebDriverBy::xpath("//li[@class='error']")
        )->getText();

        static::assertEquals(
            'At least one ACS location is required',
            $errorText
        );
    }

    public function test_it_can_requires_a_valid_acs_location_url()
    {
        $crawler = self::$pantherClient->request('GET', '/entity/create/2/saml20/test');

        // Find the asc collection entry, fill the input with a syntactically valid URL and click the + button.
        $crawler->findElement(
            WebDriverBy::xpath("//div[@class='collection-entry']/input")
        )->sendKeys('this-is-not-a-url');

        $crawler->findElement(
            WebDriverBy::xpath(
                "//div[@class='collection-entry']/button[@class='button-small blue add_collection_entry']"
            )
        )->click();
        $form = $crawler
            ->selectButton('Publish')
            ->form();

        $formData = $this->buildValidFormData();
        self::$pantherClient->submit($form, $formData);

        $errorText = self::$pantherClient->getCrawler()->findElement(
            WebDriverBy::xpath("//li[@class='error parsley-urlstrict']")
        )->getText();

        static::assertEquals(
            'This value should be a valid URL.',
            $errorText
        );
    }

    /**
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeoutException
     */
    public function test_it_can_publish_the_form()
    {
        $this->testPublicationClient->registerPublishResponse(
            'https://entity-id.url',
            '{"id":"f1e394b2-08b1-4882-8b32-43876c15c743"}'
        );
        $crawler = self::$pantherClient->request('GET', '/entity/create/2/saml20/test');

        // Find the asc collection entry, fill the input with a syntactically valid URL and click the + button.
        $crawler->findElement(
            WebDriverBy::xpath("//div[@class='collection-entry']/input")
        )->sendKeys('https://acsLocation.url');

        $crawler->findElement(
            WebDriverBy::xpath(
                "//div[@class='collection-entry']/button[@class='button-small blue add_collection_entry']"
            )
        )->click();
        $form = $crawler
            ->selectButton('Publish')
            ->form();

        $formData = $this->buildValidFormData();
        self::$pantherClient->submit($form, $formData);

        // Now register the entity in the query client (it is not actuallly stored in Manage, so we need to provide
        // test data
        $this->registerManageEntity(
            Constants::ENVIRONMENT_TEST,
            'saml20_sp',
            'f1e394b2-08b1-4882-8b32-43876c15c743',
            'The C Team',
            'https://entity-id.test',
            '',
            WebTestFixtures::TEAMNAME_IBUILDINGS,
        );
        $crawler = self::$pantherClient->reload();
        $this->assertOnPage('Connect some Idp\'s to your entity');
        // Continue without selecting test IdPs
        $form = $crawler
            ->selectButton('Save')
            ->form();
        $crawler = self::$pantherClient->submit(
            $form,
            ['idp_entity[institutionEntities][]' => '0c3febd2-3f67-4b8a-b90d-ce56a3b0abb4']
        );

        $pageTitle = $crawler->filter('h1')->first()->text();
        self::assertEquals('Successfully published the entity to test', $pageTitle);
    }

    public function test_it_does_not_show_hidden_type_of_services_as_options()
    {
        $this->expectException(NoSuchElementException::class);
        $this->expectExceptionMessage('Cannot locate option with value: SURF');

        $crawler = self::$pantherClient->request('GET', '/entity/create/2/saml20/test');

        $form = $crawler
            ->selectButton('Publish')
            ->form();

        $form['dashboard_bundle_entity_type[metadata][typeOfService][]']->setValue('Research');
        $form['dashboard_bundle_entity_type[metadata][typeOfService][]']->setValue('SURF');

        $formData = $this->buildValidFormData();
        self::$pantherClient->submit($form, $formData);
    }

    public function test_it_can_publish_multiple_acs_locations()
    {
        $this->testPublicationClient->registerPublishResponse(
            'https://entity-id.url',
            '{"id":"f1e394b2-08b1-4882-8b32-43876c15c743"}'
        );

        $crawler = self::$pantherClient->request('GET', '/entity/create/2/saml20/test');

        // Find the asc collection entry, fill the input with a syntactically valid URL and click the + button.
        $crawler->findElement(
            WebDriverBy::xpath("//div[@class='collection-entry']/input")
        )->sendKeys('https://acsLocation1.url');

        $crawler->findElement(
            WebDriverBy::xpath(
                "//div[@class='collection-entry']/button[@class='button-small blue add_collection_entry']"
            )
        )->click();

        $crawler->findElement(
            WebDriverBy::xpath("//div[@class='collection-entry']/input")
        )->sendKeys('https://acsLocation2.url');

        $crawler->findElement(
            WebDriverBy::xpath(
                "//div[@class='collection-entry']/button[@class='button-small blue add_collection_entry']"
            )
        )->click();

        $form = $crawler
            ->selectButton('Publish')
            ->form();

        $formData = $this->buildValidFormData();
        self::$pantherClient->submit($form, $formData);

        // Now register the entity in the query client (it is not actuallly stored in Manage, so we need to provide
        // test data
        $this->registerManageEntity(
            Constants::ENVIRONMENT_TEST,
            'saml20_sp',
            'f1e394b2-08b1-4882-8b32-43876c15c743',
            'The C Team',
            'https://entity-id.test',
            '',
            WebTestFixtures::TEAMNAME_IBUILDINGS,
        );
        $crawler = self::$pantherClient->reload();
        $this->assertOnPage('Connect some Idp\'s to your entity');

        $form = $crawler
            ->selectButton('Save')
            ->form();

        $checkbox = $crawler->filter('input[name="idp_entity[testEntities][]"][value="bfe8f00d-317a-4fbc-9cf8-ad2f3b2af578"]')->first();
        $checkbox->click();


        $crawler = self::$pantherClient->submit($form);

        $pageTitle = $crawler->filter('h1')->first()->text();
        self::assertEquals('Successfully published the entity to test', $pageTitle);
    }

    public function test_it_stays_on_create_action_when_publish_failed()
    {
        // Register an entity with the same entity id.
        $this->registerManageEntity(
            'test',
            'oidc10_rp',
            'f1e394b2-08b1-4882-8b32-43876c15c743',
            'Existing SP',
            'https://entity-id.url'
        );
        $formData = $this->buildValidFormData();

        $crawler = self::$pantherClient->request('GET', "/entity/create/2/saml20/test");

        $form = $crawler
            ->selectButton('Publish')
            ->form();

        $crawler = self::$pantherClient->submit($form, $formData);
        $pageTitle = $crawler->filter('h1')->first()->text();
        static::assertEquals('Service Provider registration form', $pageTitle);

        $errorMessage = $crawler->filter('div.message.error')->first()->text();
        static::assertEquals('Warning! Some entries are missing or incorrect. Please review and fix those entries below.', trim($errorMessage));

        $url = self::$pantherClient->getCurrentURL();
        static::assertMatchesRegularExpression('/\/entity\/create/', $url);
    }

    public function test_it_shows_flash_message_on_importing_invalid_metadata()
    {
        $xml = file_get_contents(__DIR__ . '/fixtures/metadata/invalid_metadata.xml');
        $formData = [
            'dashboard_bundle_entity_type[metadata][importUrl]' => '',
            'dashboard_bundle_entity_type[metadata][pastedMetadata]' => $xml,
        ];

        $crawler = self::$pantherClient->request('GET', '/entity/create/2/saml20/test');

        $form = $crawler
            ->selectButton('Import')
            ->form();

        $crawler = self::$pantherClient->submit($form, $formData);
        $message = $crawler->filter('.message.error')->first();

        self::assertEquals(
            'The provided metadata is invalid.',
            trim($message->text()),
            'Expected an error message for this invalid importUrl'
        );

        $genericMessage = $crawler->filter('.message.preformatted')->eq(0);
        $notAllowedMessage = $crawler->filter('.message.preformatted')->eq(1);
        $missingMessage = $crawler->filter('.message.preformatted')->eq(2);

        self::assertStringContainsString(
            'The metadata XML is invalid considering the associated XSD',
            $genericMessage->text(),
            'Expected an XML parse error.'
        );
        self::assertStringContainsString(
            "EntityDescriptor', attribute 'entityED': The attribute 'entityED' is not allowed.",
            $notAllowedMessage->text(),
            'Expected an XML parse error.'
        );
        self::assertStringContainsString(
            "EntityDescriptor': The attribute 'entityID' is required but missing.",
            $missingMessage->text(),
            'Expected an XML parse error.'
        );
    }

    public function test_creating_draft_for_production_is_not_allowed()
    {
        // SURFnet is not allowed to create production entities.
        $this->switchToService('SURFnet');

        $crawler = self::$pantherClient->request('GET', '/entity/create/1/saml20/production');
        self::assertOnPage('You do not have access to create entities without publishing to the test environment first');
    }

    public function test_it_imports_multiple_entity_descriptor_metadata_with_a_single_entity()
    {
        $xml = file_get_contents(__DIR__ . '/fixtures/metadata/valid_metadata_entities_descriptor.xml');

        $formData = [
            'dashboard_bundle_entity_type[metadata][importUrl]' => '',
            'dashboard_bundle_entity_type[metadata][pastedMetadata]' => $xml,
        ];
        $crawler = self::$pantherClient->request('GET', '/entity/create/2/saml20/test');

        $form = $crawler
            ->selectButton('Import')
            ->form();

        $crawler = self::$pantherClient->submit($form, $formData);

        $form = $crawler->selectButton('Publish')->form();

        // The imported metadata is loaded in the form (see: /fixtures/metadata/valid_metadata_entities_descriptor.xml)
        self::assertEquals(
            'FooBar: test instance',
            $form->get('dashboard_bundle_entity_type[metadata][nameEn]')->getValue()
        );
    }

    public function test_it_does_not_import_multiple_entity_descriptor_metadata_with_a_multiple_entities()
    {
        $xml = file_get_contents(__DIR__ . '/fixtures/metadata/invalid_metadata_entities_descriptor.xml');
        $formData = [
            'dashboard_bundle_entity_type[metadata][importUrl]' => '',
            'dashboard_bundle_entity_type[metadata][pastedMetadata]' => $xml,
        ];

        $crawler = self::$pantherClient->request('GET', "/entity/create/2/saml20/test");

        $form = $crawler
            ->selectButton('Import')
            ->form();

        $crawler = self::$pantherClient->submit($form, $formData);
        $notSupportedMultipleEntitiesMessage = $crawler->filter('.message.preformatted')->first();

        self::assertStringContainsString(
            'Using metadata that describes multiple entities is not supported. Please provide metadata describing a single SP entity.',
            $notSupportedMultipleEntitiesMessage->text(),
            'Expected an error message for unsupported multiple entities in metadata.'
        );
    }

    private function buildValidFormData(): array
    {
        $attributeName = $this->getOneAttribute()->getName();

        return [
            'dashboard_bundle_entity_type[metadata][descriptionNl]' => 'Description NL',
            'dashboard_bundle_entity_type[metadata][descriptionEn]' => 'Description EN',
            'dashboard_bundle_entity_type[metadata][nameEn]' => 'The A Team',
            'dashboard_bundle_entity_type[metadata][nameNl]' => 'The A Team',
            'dashboard_bundle_entity_type[metadata][metadataUrl]' => 'https://metadata-url.net',
            'dashboard_bundle_entity_type[metadata][entityId]' => 'https://entity-id.url',
            'dashboard_bundle_entity_type[metadata][typeOfService][]' => 'Research',
            'dashboard_bundle_entity_type[metadata][logoUrl]' =>
                'https://spdasboarddev.openconext.local/images/surfconext-logo.png',
            'dashboard_bundle_entity_type[contactInformation][administrativeContact][firstName]' => 'John',
            'dashboard_bundle_entity_type[contactInformation][administrativeContact][lastName]' => 'Doe',
            'dashboard_bundle_entity_type[contactInformation][administrativeContact][email]' => 'john@doe.com',
            'dashboard_bundle_entity_type[contactInformation][administrativeContact][phone]' => '999',
            'dashboard_bundle_entity_type[contactInformation][technicalContact][firstName]' => 'Johnny',
            'dashboard_bundle_entity_type[contactInformation][technicalContact][lastName]' => 'Doe',
            'dashboard_bundle_entity_type[contactInformation][technicalContact][email]' => 'john@doe.com',
            'dashboard_bundle_entity_type[contactInformation][technicalContact][phone]' => '888',
            'dashboard_bundle_entity_type[contactInformation][supportContact][firstName]' => 'Jack',
            'dashboard_bundle_entity_type[contactInformation][supportContact][lastName]' => 'Doe',
            'dashboard_bundle_entity_type[contactInformation][supportContact][email]' => 'john@doe.com',
            'dashboard_bundle_entity_type[contactInformation][supportContact][phone]' => '777',
            'dashboard_bundle_entity_type[attributes][' . $attributeName . '][requested]' => true,
            'dashboard_bundle_entity_type[attributes][' . $attributeName . '][motivation]' => 'We really need it!',
        ];
    }

    /**
     *  The attributes of the form are being built dynamically now, so fetch those attribute names from the
     *  attribute service and built the form data. Return exactly one attribyteType.
     */
    protected function getOneAttribute(): Attribute
    {
        $service = self::getContainer()->get('Surfnet\ServiceProviderDashboard\Application\Service\AttributeService');
        $attribute = $service->getAttributeTypeAttributes();

        return reset($attribute);
    }
}
