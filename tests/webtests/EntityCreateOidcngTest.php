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
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Symfony\Component\HttpFoundation\RedirectResponse;

class EntityCreateOidcngTest extends WebTestCase
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
        $crawler = $this->client->request('GET', "/entity/create/2/oidcng/test");
        $form = $crawler->filter('.page-container')
            ->selectButton('Save')
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
        $crawler = $this->client->request('GET', "/entity/create/2/oidcng/test");
        $form = $crawler
            ->selectButton('Cancel')
            ->form();

        $this->client->submit($form);
        // The form is now redirected to the list view
        $this->assertTrue(
            $this->client->getResponse() instanceof RedirectResponse,
            'Expecting a redirect response after saving an entity'
        );

        // The entity list queries manage for published entities
        $this->testMockHandler->append(new Response(200, [], '[]'));
        $this->testMockHandler->append(new Response(200, [], '[]'));
        $this->prodMockHandler->append(new Response(200, [], '[]'));
        $this->prodMockHandler->append(new Response(200, [], '[]'));

        $crawler = $this->client->followRedirect();
        $pageTitle = $crawler->filter('h1')->first()->text();
        $message = $crawler->filter('.page-container .card')->eq(1)->text();

        $this->assertContains("Entities of service 'Ibuildings B.V.'", $pageTitle);
        $this->assertContains('There are no entities configured', $message);
    }

    public function test_it_can_save_the_form()
    {
        $formData = $this->buildValidFormData();

        $crawler = $this->client->request('GET', "/entity/create/2/oidcng/test");

        $form = $crawler
            ->selectButton('Save')
            ->form();

        $this->client->submit($form, $formData);

        // The form is now redirected to the list view
        $this->assertTrue(
            $this->client->getResponse() instanceof RedirectResponse,
            'Expecting a redirect response after saving an entity'
        );

        // The entity list queries manage for published entities
        $this->testMockHandler->append(new Response(200, [], '[]'));
        $this->testMockHandler->append(new Response(200, [], '[]'));
        $this->prodMockHandler->append(new Response(200, [], '[]'));
        $this->prodMockHandler->append(new Response(200, [], '[]'));

        $crawler = $this->client->followRedirect();

        // Assert the entity id is in one of the td's of the first table row.
        $entityTr = $crawler->filter('.page-container table tbody tr')->first();
        $this->assertRegexp('/entity-id/', $entityTr->text());
    }

    public function test_it_can_publish_the_form()
    {
        $formData = $this->buildValidFormData();

        $crawler = $this->client->request('GET', "/entity/create/2/oidcng/test");

        $form = $crawler
            ->selectButton('Publish')
            ->form();

        $this->testMockHandler->append(new Response(200, [], '[]'));
        // ClientId validator
        $this->testMockHandler->append(new Response(200, [], '{"id":"f1e394b2-08b1-4882-8b32-43876c15c743"}'));
        // Publish json
        $this->testMockHandler->append(new Response(200, [], '{"id":"f1e394b2-08b1-4882-8b32-43876c15c743"}'));
        // Push to EB through manage
        $this->testMockHandler->append(new Response(200, [], '{"status":"OK"}'));

        $this->client->submit($form, $formData);
        // The form is now redirected to the list view
        $this->assertTrue(
            $this->client->getResponse() instanceof RedirectResponse,
            'Expecting a redirect to the published "thank you" endpoint'
        );

        // The entity list queries manage for published entities
        $this->testMockHandler->append(new Response(200, [], '[]'));
        $this->testMockHandler->append(new Response(200, [], '[]'));
        $this->prodMockHandler->append(new Response(200, [], '[]'));
        $this->prodMockHandler->append(new Response(200, [], '[]'));

        $this->client->followRedirect(); // redirect to published page
        $crawler = $this->client->followRedirect(); // redirect to list page to show secret

        // Publishing an entity saves and then attempts a publish to Manage, removing the entity afterwards in sp dash.
        $confirmation = $crawler->filter('.oidc-confirmation');
        $label = $confirmation->filter('label')->text();
        $span = $confirmation->filter('span')->text();

        // A secret should be displayed
        $this->assertEquals('Secret', $label);
        $this->assertSame(20, strlen($span));
    }

    public function test_it_forwards_to_edit_action_when_publish_failed()
    {
        $formData = $this->buildValidFormData();

        $crawler = $this->client->request('GET', "/entity/create/2/oidcng/test");

        $form = $crawler
            ->selectButton('Publish')
            ->form();

        // ClientId validator
        $this->testMockHandler->append(new Response(200, [], '{"id":"f1e394b2-08b1-4882-8b32-43876c15c743"}'));
        // Publish json
        $this->testMockHandler->append(new Response(200, [], '{"id":"f1e394b2-08b1-4882-8b32-43876c15c743"}'));
        // Push to Manage
        $this->testMockHandler->append(new Response(400, [], '{
            "timestamp": 1558969891003,
            "status": "400",
            "error": "org.springframework.web.client.HttpClientErrorException",
            "exception": "org.springframework.web.client.HttpClientErrorException",
            "message": "{\"error\":\"ClientId is already used\"}",
            "path": "//internal/metadata/"
        }'));

        $this->client->submit($form, $formData);

        // The form is now redirected to the list view
        $this->assertTrue(
            $this->client->getResponse() instanceof RedirectResponse,
            'Expecting redirect to entity edit form'
        );

        $crawler = $this->client->followRedirect();
        // Publishing an entity saves and then attempts a publish to Manage, removing the entity afterwards in sp dash.
        $pageTitle = $crawler->filter('h1')->first()->text();

        $this->assertEquals('Service Provider registration form', $pageTitle);

        $errorMessage = $crawler->filter('div.message.error')->first()->text();
        $this->assertEquals('Unable to publish the entity, try again later', trim($errorMessage));

        $uri = $this->client->getRequest()->getRequestUri();
        $this->assertRegExp('/\/entity\/edit/', $uri);
    }

    public function test_creating_draft_for_production_is_not_allowed()
    {
        // SURFnet is not allowed to create production entities.
        $this->switchToService('SURFnet');

        $this->client->request('GET', "/entity/create/1/oidcng/production");

        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function test_a_privileged_user_can_create_a_production_draft()
    {
        $formData = $this->buildValidFormData();

        $crawler = $this->client->request('GET', "/entity/create/2/oidcng/production");

        $form = $crawler
            ->selectButton('Save')
            ->form();

        $this->client->submit($form, $formData);

        // The form is now redirected to the list view
        $this->assertTrue(
            $this->client->getResponse() instanceof RedirectResponse,
            'Expecting a redirect response after saving an entity'
        );

        // The entity list queries manage for published entities
        $this->testMockHandler->append(new Response(200, [], '[]'));
        $this->testMockHandler->append(new Response(200, [], '[]'));
        $this->prodMockHandler->append(new Response(200, [], '[]'));
        $this->prodMockHandler->append(new Response(200, [], '[]'));

        $crawler = $this->client->followRedirect();

        // Assert the entity is saved for the production environment.
        $row = $crawler->filter('table tbody tr')->eq(1);

        $this->assertEquals('https://entity-id', $row->filter('td')->eq(1)->text(), 'Entity ID not found in entity list');
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
                    'isPublicClient' => true,
                    'accessTokenValidity' => 3600,
                    'logoUrl' => 'https://logo-url',
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
                'attributes' => [
                    'givenNameAttribute' => [
                        'requested' => true,
                        'motivation' => 'We really need it!',
                    ],
                ],
            ],
        ];
    }
}
