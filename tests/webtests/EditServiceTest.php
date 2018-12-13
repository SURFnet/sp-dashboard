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

use GuzzleHttp\Psr7\Response;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\RedirectResponse;

class EditServiceTest extends WebTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->loadFixtures();

        $this->getAuthorizationService()->setSelectedServiceId(
            $this->getServiceRepository()->findByName('SURFnet')->getId()
        );

        $this->testMockHandler->append(new Response(200, [], '[]'));
        $this->prodMockHandler->append(new Response(200, [], '[]'));
    }

    public function test_can_edit_existing_service()
    {
        $this->logIn('ROLE_ADMINISTRATOR');

        $formData = [
            'dashboard_bundle_edit_service_type' => [
                'general' => [
                    'guid' => 'f1af6b9e-2546-4593-a57f-6ca34d2561e9',
                    'name' => 'The A Team',
                    'teamName' => 'team-a',
                ]
            ]
        ];

        // EntityService::getEntityListForService -> findByTeamName
        $this->testMockHandler->append(new Response(200, [], '[]'));
        $this->prodMockHandler->append(new Response(200, [], '[]'));

        $crawler = $this->client->request('GET', '/service/1/edit');

        $form = $crawler
            ->selectButton('Save')
            ->form();

        $this->client->submit($form, $formData);

        $service = $this->getServiceRepository()->findAll()[0];

        $this->assertEquals('f1af6b9e-2546-4593-a57f-6ca34d2561e9', $service->getGuid());
        $this->assertEquals('The A Team', $service->getName());
        $this->assertEquals('team-a', $service->getTeamName());
    }

    /**
     * Admins can toggle the privacy question feature for Services. Effectively enabling/disabling the Privacy
     * question form.
     */
    public function test_privacy_questions_admin_toggle()
    {
        $serviceRepository = $this->getServiceRepository();

        $this->logIn('ROLE_ADMINISTRATOR');

        // EntityService::getEntityListForService -> findByTeamName
        $this->testMockHandler->append(new Response(200, [], '[]'));
        $this->prodMockHandler->append(new Response(200, [], '[]'));

        $crawler = $this->client->request('GET', '/service/1/edit');

        // Step 1: Admin sets privacy questions enabled to false
        $formData = [
            'dashboard_bundle_edit_service_type' => [
                'general' => [
                    'privacyQuestionsEnabled' => false,
                ]
            ]
        ];

        $form = $crawler
            ->selectButton('Save')
            ->form();

        $this->client->submit($form, $formData);

        // Step 2: Surfnet can't access the privacy questions
        $surfNet = $serviceRepository->findByName('SURFnet');
        $this->logIn('ROLE_USER', [$surfNet]);

        $this->client->request('GET', '/service/1/privacy');

        $this->assertEquals(
            404,
            $this->client->getResponse()->getStatusCode(),
            'Privacy questions page is not visitable.'
        );

        // Step 3: Admin enables the Privacy questions
        $this->logIn('ROLE_ADMINISTRATOR');

        // EntityService::getEntityListForService -> findByTeamName (is called twice on both test and prod)
        $this->testMockHandler->append(new Response(200, [], '[]'));
        $this->testMockHandler->append(new Response(200, [], '[]'));
        $this->prodMockHandler->append(new Response(200, [], '[]'));
        $this->prodMockHandler->append(new Response(200, [], '[]'));

        $crawler = $this->client->request('GET', '/service/1/edit');

        $formData = [
            'dashboard_bundle_edit_service_type' => [
                'general' => [
                    'privacyQuestionsEnabled' => true,
                ]
            ]
        ];

        $form = $crawler
            ->selectButton('Save')
            ->form();

        $this->client->submit($form, $formData);

        $this->testMockHandler->append(new Response(200, [], '[]'));
        $this->prodMockHandler->append(new Response(200, [], '[]'));

        $this->client->request('GET', '/service/1/privacy');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
}
