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

        // Note: after a redirect response we do not have access to the container anymore.
        // To work around this problem, we do the setup again and visit the redirection target.
        //
        // See: https://github.com/symfony/symfony/issues/8858
        $this->setUp();

        // Step 4: Surfnet can access the privacy questions
        $this->logIn('ROLE_USER', [$surfNet]);

        $this->mockHandler->append(new Response(200, [], '[]'));

        $crawler = $this->client->request('GET', '/');

        $navTexts = $crawler->filterXPath('//div[@class="navigation"]/ul/li/a/text()')->extract(['_text']);

        $this->assertContains('Privacy', $navTexts);

        $this->client->request('GET', '/service/privacy');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
}
