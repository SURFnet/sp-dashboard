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
            "Expected to be on the service overview page after succesfully removing the service"
        );

        // TODO: Find a more robust way to test if the enity was removed.
        $services = $crawler->filterXPath('//div[@class="service-status-title"]/a/text()')->extract(['_text']);

        $this->assertNotContains(
            'SURFnet',
            $services,
            "The SURFnet Service has been removed and should no longer be on the service overview page"
        );
    }
}
