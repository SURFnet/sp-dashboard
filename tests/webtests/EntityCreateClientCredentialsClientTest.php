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
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;

class EntityCreateClientCredentialsClientTest extends WebTestCase
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
        $crawler = self::$pantherClient->request('GET', "/entity/create/2/oauth20_ccc/test");
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

    public function test_no_attributes_on_the_form()
    {
        $crawler = self::$pantherClient->request('GET', "/entity/create/2/oauth20_ccc/test");
        // Manage is queried for resource servers
        $form = $crawler->filter('.page-container')
            ->selectButton('Publish')
            ->form();

        // CCC entities do not have any attributes
        $this->expectException(NoSuchElementException::class);
        $form->get('dashboard_bundle_entity_type[attributes][eduPersonTargetedIDAttribute][requested]');
    }

    public function test_it_can_cancel_out_of_the_form()
    {
        $crawler = self::$pantherClient->request('GET', "/entity/create/2/oauth20_ccc/test");
        $form = $crawler
            ->selectButton('Cancel')
            ->form();

        $crawler = self::$pantherClient->submit($form);
        $pageTitle = $crawler->filter('.service-title')->first()->text();
        $messageTest = $crawler->filter('.no-entities-test')->text();
        $messageProduction = $crawler->filter('.no-entities-production')->text();

        $this->assertStringContainsString("Ibuildings B.V. overview", $pageTitle);
        $this->assertStringContainsString('No entities found.', $messageTest);
        $this->assertStringContainsString('No entities found.', $messageProduction);
    }

    public function test_it_can_publish_the_form()
    {
        $this->testPublicationClient->registerPublishResponse('https://entity-id.org', '{"id":"f1e394b2-08b1-4882-8b32-43876c15c743"}');
        $formData = $this->buildValidFormData();
        $crawler = self::$pantherClient->request('GET', "/entity/create/2/oauth20_ccc/test");

        $form = $crawler
            ->selectButton('Publish')
            ->form();

        $crawler = self::$pantherClient->submit($form, $formData);
        $confirmation = $crawler->filter('.oidc-confirmation');
        $label = $confirmation->filter('label')->text();
        $secret = $confirmation->filter('pre')->text();

        // A secret should be displayed
        $this->assertEquals('Secret', $label);
        $this->assertSame(20, strlen($secret));
    }

    public function test_it_stays_on_create_action_when_publish_failed()
    {
        // Register an enitty with the same entity id.
        $this->registerManageEntity(
            'test',
            Constants::TYPE_OAUTH_CLIENT_CREDENTIAL_CLIENT,
            'f1e394b2-08b1-4882-8b32-43876c15c743',
            'Existing RP',
            'entity-id.org'
        );

        $formData = $this->buildValidFormData();

        $crawler = self::$pantherClient->request('GET', "/entity/create/2/oauth20_ccc/test");

        $form = $crawler
            ->selectButton('Publish')
            ->form();

        self::$pantherClient->submit($form, $formData);
        self::assertOnPage('Service Provider registration form');
        self::assertOnPage('Entity has already been registered.');
        self::assertOnPage('Warning! Some entries are missing or incorrect. Please review and fix those entries below.');
    }

    public function test_creating_draft_for_production_is_not_allowed()
    {
        $this->switchToService('SURFnet');

        self::$pantherClient->request('GET', "/entity/create/1/oauth20_ccc/production");
        self::assertOnPage('You do not have access to create entities without publishing to the test environment first');
    }

    private function buildValidFormData()
    {
        return [
            'dashboard_bundle_entity_type[metadata][descriptionNl]' => 'Description NL',
            'dashboard_bundle_entity_type[metadata][descriptionEn]' => 'Description EN',
            'dashboard_bundle_entity_type[metadata][nameEn]' => 'The C Team',
            'dashboard_bundle_entity_type[metadata][nameNl]' => 'The C Team',
            'dashboard_bundle_entity_type[metadata][clientId]' => 'https://entity-id.org',
            'dashboard_bundle_entity_type[metadata][logoUrl]' => 'https://logo-url.org/picture.png',
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
