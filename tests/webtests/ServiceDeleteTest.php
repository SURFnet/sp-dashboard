<?php

/**
 * Copyright 2018 SURFnet B.V.
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

class ServiceDeleteTest extends WebTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->loadFixtures();

        $this->getAuthorizationService()->setSelectedServiceId(
            $this->getServiceRepository()->findByName('SURFnet')->getId()
        );
    }

    public function test_removing_a_service_redirects_to_service_overview()
    {
        $this->logIn('ROLE_ADMINISTRATOR');

        // EntityService::getEntityListForService -> findByTeamName (service/edit first request)
        $this->testMockHandler->append(new Response(200, [], '[]'));
        $this->prodMockHandler->append(new Response(200, [], '[]'));

        // EntityService::getEntityListForService -> findByTeamName (service/edit after delete button click)
        $this->testMockHandler->append(new Response(200, [], '[]'));
        $this->prodMockHandler->append(new Response(200, [], '[]'));

        // The entities are listed on the delete confirmation page (page is visited twice)
        $this->testMockHandler->append(new Response(200, [], '[]'));
        $this->prodMockHandler->append(new Response(200, [], '[]'));
        $this->testMockHandler->append(new Response(200, [], '[]'));
        $this->prodMockHandler->append(new Response(200, [], '[]'));

        // EntityService::getEntityListForService -> getEntityListForService
        $this->testMockHandler->append(new Response(200, [], '[]'));
        $this->prodMockHandler->append(new Response(200, [], '[]'));

        $crawler = $this->client->request('GET', '/service/1/edit');

        $form = $crawler
            ->selectButton('Delete')
            ->form();

        $this->client->submit($form);

        $this->assertTrue(
            $this->client->getResponse() instanceof RedirectResponse,
            'Expecting a redirect response after deleting a service'
        );

        $crawler = $this->client->followRedirect();

        $this->assertEquals(
            '/service/1/delete',
            $this->client->getRequest()->getRequestUri(),
            "Expected to be on the service delete confirmation page"
        );

        // Assert the entities of the service are listed on the page
        $entities = $crawler->filter('table.entities tbody tr');
        // The two SURFnet entities are in the list.
        $this->assertCount(2, $entities, 'The two pre configured entities should be listed on the confirmation page');

        $form = $crawler
            ->selectButton('Delete')
            ->form();

        $this->client->submit($form);

        $this->assertTrue(
            $this->client->getResponse() instanceof RedirectResponse,
            'Expecting a redirect response after pressing the delete button on the confirmation page'
        );

        $crawler = $this->client->followRedirect();

        $this->assertEquals(
            '/',
            $this->client->getRequest()->getRequestUri(),
            "Expected to be on the service overview page after successfully removing the service"
        );

        // TODO: Find a more robust way to test if the enity was removed.
        $services = $crawler->filterXPath('//div[@class="service-status-title"]/a/text()')->extract(['_text']);

        $this->assertNotContains(
            'SURFnet',
            $services,
            "The SURFnet Service has been removed and should no longer be on the service overview page"
        );
    }

    /**
     * Removing a service with privacy questions should not result in integrity constraint violation errors
     *
     * See Pivotal Tracker; https://www.pivotaltracker.com/story/show/165237921
     */
    public function test_removing_a_service_with_privacy_questions_is_possible()
    {
        $this->logIn('ROLE_ADMINISTRATOR');

        // EntityService::getEntityListForService -> findByTeamName (service/edit first request)
        $this->testMockHandler->append(new Response(200, [], '[]'));
        $this->prodMockHandler->append(new Response(200, [], '[]'));

        // EntityService::getEntityListForService -> findByTeamName (service/edit after delete button click)
        $this->testMockHandler->append(new Response(200, [], '[]'));
        $this->prodMockHandler->append(new Response(200, [], '[]'));

        // The entities are listed on the delete confirmation page (page is visited twice)
        $this->testMockHandler->append(new Response(200, [], '[]'));
        $this->prodMockHandler->append(new Response(200, [], '[]'));
        $this->testMockHandler->append(new Response(200, [], '[]'));
        $this->prodMockHandler->append(new Response(200, [], '[]'));

        // EntityService::getEntityListForService -> getEntityListForService
        $this->testMockHandler->append(new Response(200, [], '[]'));
        $this->prodMockHandler->append(new Response(200, [], '[]'));

        $crawler = $this->client->request('GET', '/service/2/edit');

        $form = $crawler
            ->selectButton('Delete')
            ->form();

        $this->client->submit($form);

        $this->assertTrue(
            $this->client->getResponse() instanceof RedirectResponse,
            'Expecting a redirect response after deleting a service'
        );

        $crawler = $this->client->followRedirect();

        $this->assertEquals(
            '/service/2/delete',
            $this->client->getRequest()->getRequestUri(),
            "Expected to be on the service delete confirmation page"
        );

        $form = $crawler
            ->selectButton('Delete')
            ->form();

        $this->client->submit($form);

        $this->assertTrue(
            $this->client->getResponse() instanceof RedirectResponse,
            'Expecting a redirect response after pressing the delete button on the confirmation page'
        );

        $crawler = $this->client->followRedirect();

        $this->assertEquals(
            '/',
            $this->client->getRequest()->getRequestUri(),
            "Expected to be on the service overview page after successfully removing the service"
        );

        // TODO: Find a more robust way to test if the enity was removed.
        $services = $crawler->filterXPath('//div[@class="service-status-title"]/a/text()')->extract(['_text']);

        $this->assertNotContains(
            'Ibuildings B.V.',
            $services,
            "The Ibuildings B.V. Service has been removed and should no longer be on the service overview page"
        );
    }

}
