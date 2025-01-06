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

use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\WebDriverBy;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\DataFixtures\ORM\WebTestFixtures;

class EntityCreateOidcngTest extends WebTestCase
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
        // Manage is queried for resource servers
        $crawler = self::$pantherClient->request('GET', "/entity/create/2/oidcng/test");
        $form = $crawler->filter('.page-container')
            ->selectButton('Publish')
            ->form();

        $nameEnfield = $form->get('dashboard_bundle_entity_type[metadata][nameEn]');

        $this->assertEquals(
            '',
            $nameEnfield->getValue(),
            'Expect the NameEN field to be empty'
        );
    }

    public function test_epti_attribute_is_not_on_the_form()
    {
        $crawler = self::$pantherClient->request('GET', "/entity/create/2/oidcng/test");
        // Manage is queried for resource servers
        $form = $crawler->filter('.page-container')
            ->selectButton('Publish')
            ->form();

        // OIDC NG entities do not have the epti attribute as an ARP option. As it's enabled by default.
        // See: https://www.pivotaltracker.com/story/show/167511328
        $this->expectException(NoSuchElementException::class);
        $form->get('dashboard_bundle_entity_type[attributes][eduPersonTargetedIDAttribute][requested]');
    }

    public function test_it_can_cancel_out_of_the_form()
    {
        $crawler = self::$pantherClient->request('GET', "/entity/create/2/oidcng/test");
        $form = $crawler
            ->selectButton('Cancel')
            ->form();

        $crawler = self::$pantherClient->submit($form);
        // The form is now redirected to the list view
        $pageTitle = $crawler->filter('.service-title')->first()->text();
        $messageTest = $crawler->filter('.no-entities-test')->text();
        $messageProduction = $crawler->filter('.no-entities-production')->text();

        $this->assertStringContainsString("Ibuildings B.V. overview", $pageTitle);
        $this->assertStringContainsString('No entities found.', $messageTest);
        $this->assertStringContainsString('No entities found.', $messageProduction);
    }

    public function test_one_redirect_url_is_required()
    {
        $this->testPublicationClient->registerPublishResponse('https://entity-id.test', '{"id":"f1e394b2-08b1-4882-8b32-43876c15c743"}');
        $formData = $this->buildValidFormData();
        $crawler = self::$pantherClient->request('GET', "/entity/create/2/oidcng/test");

        $form = $crawler
            ->selectButton('Publish')
            ->form();

        self::$pantherClient->submit($form, $formData);
        self::assertOnPage('You need to add a minimum of 1 redirect Url.');
    }

    public function test_it_can_publish_the_form()
    {
        $this->testPublicationClient->registerPublishResponse('https://entity-id.test', '{"id":"f1e394b2-08b1-4882-8b32-43876c15c743"}');
        $formData = $this->buildValidFormData();
        $crawler = self::$pantherClient->request('GET', "/entity/create/2/oidcng/test");

        $form = $crawler
            ->selectButton('Publish')
            ->form();

        $crawler = self::$pantherClient->submit($form, $formData);

        // Now add a redirect Url
        $form = $crawler->findElement(WebDriverBy::cssSelector('form[name="dashboard_bundle_entity_type"]'));

        $this->fillFormField($form, '#dashboard_bundle_entity_type_metadata_redirectUrls .collection-entry input', 'https://redirect-url.example.com');
        $this->click($form, '.add_collection_entry');
        $this->click($form, '#dashboard_bundle_entity_type_publishButton');
        // Now register the entity in the query client (it is not actuallly stored in Manage, so we need to provide
        // test data
        $this->registerManageEntity(
            Constants::ENVIRONMENT_TEST,
            'oidc10_rp',
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

        $label = $crawler->filter('.monospaced label')->first()->text();
        $span = $crawler->filter('.monospaced pre')->first()->text();
        // A secret should be displayed
        $this->assertEquals('Secret', $label);
        $this->assertSame(20, strlen($span));
    }

    public function test_it_stays_on_create_action_when_publish_failed()
    {
        // Register an entity with the same entity id.
        $this->registerManageEntity(
            'test',
            'oidc10_rp',
            'f1e394b2-08b1-4882-8b32-43876c15c743',
            'Existing RP',
            'entity-id.test'
        );

        $formData = $this->buildValidFormData();

        $crawler = self::$pantherClient->request('GET', "/entity/create/2/oidcng/test");

        $form = $crawler
            ->selectButton('Publish')
            ->form();
        $crawler = self::$pantherClient->submit($form, $formData);

        $pageTitle = $crawler->filter('h1')->first()->text();
        $this->assertEquals('Service Provider registration form', $pageTitle);

        $errorMessage = $crawler->filter('div.message.error')->first()->text();
        $this->assertEquals('Warning! Some entries are missing or incorrect. Please review and fix those entries below.', trim($errorMessage));
    }

    public function test_creating_draft_for_production_is_not_allowed()
    {
        // SURFnet is not allowed to create production entities.
        $this->switchToService('SURFnet');

        self::$pantherClient->request('GET', "/entity/create/1/oidcng/production");
        self::assertOnPage('You do not have access to create entities without publishing to the test environment first');
    }

    private function buildValidFormData()
    {
        return [
            'dashboard_bundle_entity_type[metadata][descriptionNl]' => 'Description NL',
            'dashboard_bundle_entity_type[metadata][descriptionEn]' => 'Description EN',
            'dashboard_bundle_entity_type[metadata][nameEn]' => 'The C Team',
            'dashboard_bundle_entity_type[metadata][nameNl]' => 'The C Team',
            'dashboard_bundle_entity_type[metadata][clientId]' => 'https://entity-id.test',
            'dashboard_bundle_entity_type[metadata][logoUrl]' => 'https://scriptmag.com/.image/t_share/MTY3Mzc5MDUyOTIyNTQ1OTY1/image-placeholder-title.png',
            'dashboard_bundle_entity_type[metadata][isPublicClient]' => true,
            'dashboard_bundle_entity_type[metadata][grants]' => ['authorization_code'],
            'dashboard_bundle_entity_type[metadata][accessTokenValidity]' => '3600',
//            'dashboard_bundle_entity_type[metadata][typeOfService][]' => 'Research',
            'dashboard_bundle_entity_type[attributes][displayNameAttribute][requested]' => true,
            'dashboard_bundle_entity_type[attributes][displayNameAttribute][motivation]' => 'We really need it!',
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
        ];
    }
}
