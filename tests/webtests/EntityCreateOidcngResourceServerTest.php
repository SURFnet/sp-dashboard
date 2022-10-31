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

use GuzzleHttp\Psr7\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class EntityCreateOidcngResourceServerTest extends WebTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->loadFixtures();

        $this->logIn('ROLE_ADMINISTRATOR');

        $this->switchToService('Ibuildings B.V.');
    }

    public function test_it_renders_the_form()
    {
        $crawler = $this->client->request('GET', "/entity/create/2/oauth20_rs/test");
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

    public function test_it_can_cancel_out_of_the_form()
    {
        $crawler = $this->client->request('GET', "/entity/create/2/oauth20_rs/test");
        $form = $crawler
            ->selectButton('Cancel')
            ->form();

        $this->client->submit($form);
        // The form is now redirected to the list view
        $this->assertTrue(
            $this->client->getResponse() instanceof RedirectResponse,
            'Expecting a redirect response after saving an entity'
        );

        $crawler = $this->client->followRedirect();
        $pageTitle = $crawler->filter('.service-title')->first()->text();
        $messageTest = $crawler->filter('.no-entities-test')->text();
        $messageProduction = $crawler->filter('.no-entities-production')->text();

        $this->assertContains("Ibuildings B.V. overview", $pageTitle);
        $this->assertContains('No entities found.', $messageTest);
        $this->assertContains('No entities found.', $messageProduction);
    }

    public function test_it_can_not_save_the_form_drafts_are_disabled()
    {
        $crawler = $this->client->request('GET', "/entity/create/2/oauth20_rs/test");

        // There is a publish button instead
        $buttonNodes = $crawler->filter('button#dashboard_bundle_entity_type_publishButton');
        $this->assertEquals(1, $buttonNodes->count());

        // And the cancel button is ever present
        $buttonNodes = $crawler->filter('button#dashboard_bundle_entity_type_cancel');
        $this->assertEquals(1, $buttonNodes->count());
    }

    public function test_it_can_publish_the_form()
    {
        $this->testPublicationClient->registerPublishResponse('https://entity-id', '{"id":"f1e394b2-08b1-4882-8b32-43876c15c743"}');
        $formData = $this->buildValidFormData();

        $crawler = $this->client->request('GET', "/entity/create/2/oauth20_rs/test");

        $form = $crawler
            ->selectButton('Publish')
            ->form();

        $this->client->submit($form, $formData);
        // The form is now redirected to the list view
        $this->assertTrue(
            $this->client->getResponse() instanceof RedirectResponse,
            'Expecting a redirect to the published "thank you" endpoint'
        );

        $this->client->followRedirect(); // redirect to published page
        $crawler = $this->client->followRedirect(); // redirect to list page to show secret

        $confirmation = $crawler->filter('.oidc-confirmation');
        $label = $confirmation->filter('label')->text();
        $secret = $confirmation->filter('pre')->text();

        // A secret should be displayed
        $this->assertEquals('Secret', $label);
        $this->assertSame(20, strlen($secret));

        // When a new entity is published, a flash message should be displayed
        $flashMessage = $crawler->filter('.flashMessage.wysiwyg')->text();
        $this->assertContains('Select OIDC RP to connect this Resource Server to', $flashMessage);
    }

    public function test_it_stays_on_create_action_when_publication_fails()
    {
        // Register an enitty with the same entity id.
        $this->registerManageEntity(
            'test',
            'oidc10_rp',
            'f1e394b2-08b1-4882-8b32-43876c15c743',
            'Existing RP',
            'entity-id'
        );

        $formData = $this->buildValidFormData();
        $crawler = $this->client->request('GET', "/entity/create/2/oauth20_rs/test");

        $form = $crawler
            ->selectButton('Publish')
            ->form();

        $crawler = $this->client->submit($form, $formData);
        $pageTitle = $crawler->filter('h1')->first()->text();

        $this->assertEquals('Service Provider registration form', $pageTitle);
        $errorMessage = $crawler->filter('li.error')->first()->text();
        $this->assertEquals('Entity has already been registered.', trim($errorMessage));

        $uri = $this->client->getRequest()->getRequestUri();
        $this->assertRegExp('/\/entity\/create/', $uri);
    }

    public function test_creating_draft_for_production_is_not_allowed()
    {
        // SURFnet is not allowed to create production entities.
        $this->switchToService('SURFnet');

        $this->client->request('GET', "/entity/create/1/oauth20_rs/production");

        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    private function buildValidFormData()
    {
        return [
            'dashboard_bundle_entity_type' => [
                'metadata' => [
                    'descriptionNl' => 'Description NL',
                    'descriptionEn' => 'Description EN',
                    'nameEn' => 'The A Team',
                    'nameNl' => 'The A Team',
                    'clientId' => 'https://entity-id',
                ],
                'contactInformation' => [
                    'administrativeContact' => [
                        'firstName' => 'John',
                        'lastName' => 'Doe',
                        'email' => 'john@doe.com',
                        'phone' => '999',
                    ],
                    'technicalContact' => [
                        'firstName' => 'Johnny',
                        'lastName' => 'Doe',
                        'email' => 'john@doe.com',
                        'phone' => '888',
                    ],
                    'supportContact' => [
                        'firstName' => 'Jack',
                        'lastName' => 'Doe',
                        'email' => 'john@doe.com',
                        'phone' => '777',
                    ],
                ],
            ],
        ];
    }
}
