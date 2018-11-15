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
                'guid' => 'f1af6b9e-2546-4593-a57f-6ca34d2561e9',
                'name' => 'The A Team',
                'teamName' => 'team-a',
            ]
        ];

        // EntityService::getEntityListForService -> findByTeamName
        $this->testMockHandler->append(new Response(200, [], '[]'));
        $this->prodMockHandler->append(new Response(200, [], '[]'));

        $crawler = $this->client->request('GET', '/service/edit');

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

        $crawler = $this->client->request('GET', '/service/edit');

        // Step 1: Admin sets privacy questions enabled to false
        $formData = [
            'dashboard_bundle_edit_service_type' => [
                'privacyQuestionsEnabled' => false,
            ]
        ];

        $form = $crawler
            ->selectButton('Save')
            ->form();

        $this->client->submit($form, $formData);

        // Step 2: Surfnet can't access the privacy questions
        $surfNet = $serviceRepository->findByName('SURFnet');
        $this->logIn('ROLE_USER', [$surfNet]);

        $crawler = $this->client->request('GET', '/');
        $navTexts = $crawler->filterXPath('//div[@class="navigation"]/ul/li/a/text()')->extract(['_text']);

        $this->assertNotContains('Privacy', $navTexts, 'The Privacy Questions entry should not be in the navigation panel.');

        $this->client->request('GET', '/service/privacy');

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

        $crawler = $this->client->request('GET', '/service/edit');

        $formData = [
            'dashboard_bundle_edit_service_type' => [
                'privacyQuestionsEnabled' => true,
            ]
        ];

        $form = $crawler
            ->selectButton('Save')
            ->form();

        $this->client->submit($form, $formData);

        $this->testMockHandler->append(new Response(200, [], '[]'));
        $this->prodMockHandler->append(new Response(200, [], '[]'));

        $crawler = $this->client->followRedirect();

        $navTexts = $crawler->filterXPath('//div[@class="navigation"]/ul/li/a/text()')->extract(['_text']);

        $this->assertContains('Privacy', $navTexts);

        $this->client->request('GET', '/service/privacy');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function test_privacy_questions_filled_status_is_reflected_correctly()
    {
        // Log in with Ibuildings
        $this->logIn('ROLE_ADMINISTRATOR');
        $this->loadFixtures();
        $this->switchToService('Ibuildings B.V.');

        // EntityService::getEntityListForService -> findByTeamName
        $this->testMockHandler->append(new Response(200, [], '[]'));
        $this->prodMockHandler->append(new Response(200, [], '[]'));

        $crawler = $this->client->request('GET', '/service/edit');

        $radio = $crawler->filter('#dashboard_bundle_edit_service_type_privacyQuestionsAnswered_1');
        // The checked element should be the 'Yes' radio option as the ibuildings service has a privacy questions entity
        $this->assertEquals(1, $radio->attr('value'));
        // The radio is disabled
        $this->assertEquals('disabled', $radio->attr('disabled'));
    }
}
