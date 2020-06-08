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

use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ServiceCreateTest extends WebTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->loadFixtures();
    }

    public function test_it_validates_the_form()
    {
        $this->logIn('ROLE_ADMINISTRATOR');

        $formData = [
            'dashboard_bundle_service_type' => [
                'general' => [
                    'guid' => 'a8a1fa6f-bffd-xxyz-874a-b9f4fdf92942',
                    'name' => 'The A Team',
                    'teamName' => 'team-a',
                ]
            ]
        ];

        $crawler = $this->client->request('GET', '/service/create');

        $form = $crawler
            ->selectButton('Save')
            ->form();

        $crawler = $this->client->submit($form, $formData);


        $nodes = $crawler->filter('#dashboard_bundle_service_type_general li');
        $this->assertEquals('This is not a valid UUID.', $nodes->first()->text());
    }

    public function test_empty_guid_field_is_allowed()
    {
        $this->logIn('ROLE_ADMINISTRATOR');

        $formData = [
            'dashboard_bundle_service_type' => [
                'general' => [
                    'guid' => '',
                    'name' => 'The A Team',
                    'teamName' => 'team-a',
                ]
            ]
        ];

        $crawler = $this->client->request('GET', '/service/create');

        $form = $crawler
            ->selectButton('Save')
            ->form();

        $this->client->submit($form, $formData);

        $this->assertTrue(
            $this->client->getResponse() instanceof RedirectResponse,
            'Expecting a redirect response after adding a service'
        );

        $services = $this->getServiceRepository()->findAll();
        $this->assertCount(3, $services);
    }

    public function test_it_validates_guid_correctly()
    {
        $this->logIn('ROLE_ADMINISTRATOR');

        // This UUID is not compliant to the RFC-4122 spec, but is a valid GUID
        $formData = [
            'dashboard_bundle_service_type' => [
                'general' => [
                    'guid' => '1234abcd-146e-e711-80e8-005056956c1e',
                    'name' => 'The A Team',
                    'teamName' => 'team-a',
                ]
            ]
        ];

        $crawler = $this->client->request('GET', '/service/create');

        $form = $crawler
            ->selectButton('Save')
            ->form();

        $this->client->submit($form, $formData);

        $this->assertTrue(
            $this->client->getResponse() instanceof RedirectResponse,
            'Expecting a redirect response after adding a service'
        );
    }

    public function test_it_rejects_duplicate_teamnames()
    {
        $this->logIn('ROLE_ADMINISTRATOR');

        $formData = [
            'dashboard_bundle_service_type' => [
                'general' => [
                    'guid' => Uuid::uuid4(),
                    'name' => 'The A Team',
                    'teamName' => 'urn:collab:org:surf.nl',
                ]
            ]
        ];

        $crawler = $this->client->request('GET', '/service/create');

        $form = $crawler
            ->selectButton('Save')
            ->form();

        $crawler = $this->client->submit($form, $formData);

        $nodes = $crawler->filter('.page-container .message.error');

        $this->assertEquals(
            'The teamname of the service should be unique. This teamname is taken by: "SURFnet"',
            trim($nodes->first()->text())
        );
    }

    public function test_can_create_new_service()
    {
        $this->logIn('ROLE_ADMINISTRATOR');

        $formData = [
            'dashboard_bundle_service_type' => [
                'general' => [
                    'guid' => 'b9aaa8c4-3376-4e9d-b828-afa38cf29986',
                    'name' => 'The A Team',
                    'teamName' => 'team-a',
                ]
            ]
        ];

        $crawler = $this->client->request('GET', '/service/create');

        $form = $crawler
            ->selectButton('Save')
            ->form();

        $this->client->submit($form, $formData);

        $this->assertTrue(
            $this->client->getResponse() instanceof RedirectResponse,
            'Expecting a redirect response after adding a service'
        );

        $services = $this->getServiceRepository()->findAll();
        $this->assertCount(3, $services);
    }
}
