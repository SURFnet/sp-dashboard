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
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ServiceOverviewTest extends WebTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->loadFixtures();

        $this->getAuthorizationService()->setSelectedServiceId(
            $this->getServiceRepository()->findByName('SURFnet')->getId()
        );
    }

    /**
     * From the services overview page we summarize the available services
     * for the contact that is currently logged in
     *
     * Todo: add tests for the entity form
     */
    public function test_users_can_use_the_service_overview_page()
    {
        $serviceRepository = $this->getServiceRepository();
        $surfNet = $serviceRepository->findByName('SURFnet');

        $this->testMockHandler->append(new Response(200, [], '[]'));
        $this->prodMockHandler->append(new Response(200, [], '[]'));

        $this->logIn('ROLE_USER', [$surfNet]);

        $crawler = $this->client->request('GET', '/');

        // By retrieving the h1 titles (stating the services) we can conclude if the correct data is displayed.
        $nodes = $crawler->filter('.service-status-container .service-status-title');
        $serviceNode = $nodes->first();

        $this->assertEquals('SURFnet', trim($serviceNode->text()));
    }

    /**
     * Multiple services can be listed on the page
     */
    public function test_multiple_services_can_be_listed()
    {
        $serviceRepository = $this->getServiceRepository();
        $surfNet = $serviceRepository->findByName('SURFnet');
        $ibuildings = $serviceRepository->findByName('Ibuildings B.V.');

        $this->testMockHandler->append(new Response(200, [], '[]'));
        $this->testMockHandler->append(new Response(200, [], '[]'));
        $this->prodMockHandler->append(new Response(200, [], '[]'));
        $this->prodMockHandler->append(new Response(200, [], '[]'));

        $this->logIn('ROLE_USER', [$surfNet, $ibuildings]);

        $crawler = $this->client->request('GET', '/');

        // By retrieving the h1 titles (stating the services) we can conclude if the correct data is displayed.
        $nodes = $crawler->filter('.service-status-container .service-status-title');

        // Two services should be on page: surf and ibuildings.
        $this->assertEquals(2, $nodes->count());

        // The two nodes are sorted alphabetically.
        $serviceNode = $nodes->first();
        $service2 = $nodes->eq(1);

        $this->assertEquals('Ibuildings B.V.', trim($serviceNode->text()));
        $this->assertEquals('SURFnet', trim($service2->text()));
    }

    /**
     * Entities of a service are listed on the page
     */
    public function test_entitites_of_a_service_are_listed()
    {
        $serviceRepository = $this->getServiceRepository();
        $surfNet = $serviceRepository->findByName('SURFnet');
        $ibuildings = $serviceRepository->findByName('Ibuildings B.V.');

        $this->testMockHandler->append(new Response(200, [], '[]'));
        $this->testMockHandler->append(new Response(200, [], '[]'));
        $this->prodMockHandler->append(new Response(200, [], '[]'));
        $this->prodMockHandler->append(new Response(200, [], '[]'));

        $this->logIn('ROLE_USER', [$surfNet, $ibuildings]);

        $crawler = $this->client->request('GET', '/');

        // By retrieving the h1 titles (stating the services) we can conclude if the correct data is displayed.
        $nodes = $crawler->filter('.service-status-container');

        // Two services should be on page: surf and ibuildings.
        $this->assertEquals(2, $nodes->count());

        $serviceNode = $nodes->first();
        $service2 = $nodes->eq(1);

        $tableService1 = $serviceNode->filter('.service-status-entities');
        $tableService2 = $service2->filter('.service-status-entities');

        $result1 = $this->rowsToArray($tableService1);
        $result2 = $this->rowsToArray($tableService2);

        $this->assertEquals([
            ['Entities @ production environment'],
            ['No entities found.'],
            [''],
            ['Entities @ test environment'],
            ['No entities found.'],
        ], $result1);
        $this->assertEquals([
            ['Entities @ production environment'],
            ['No entities found.'],
            [""],
            ['Entities @ test environment'],
            ["SP1", "SP1", "SAML", "draft"],
            ["SP2", "SP2", "SAML", "draft"],
        ], $result2);
    }

    public function test_service_overview_redirects_to_service_add_when_no_service_exists()
    {
        $this->clearFixtures();
        $this->logIn('ROLE_ADMINISTRATOR');

        $this->client->request('GET', '/');
        $response = $this->client->getResponse();

        $this->assertTrue(
            $response instanceof RedirectResponse,
            'Expecting a redirect response to add form when no service exists'
        );

        $this->assertRegExp('#service/create$#', $response->headers->get('location'));
    }

    public function test_service_overview_shows_message_when_no_service_selected()
    {
        $this->markTestSkipped('TODO: Determine what the Administrator should see when authenticated');

        $this->loadFixtures();
        $this->logIn('ROLE_ADMINISTRATOR');

        $this->client->request('GET', '/');
        $response = $this->client->getResponse();

        $this->assertContains('No service selected', $response->getContent());
        $this->assertContains('Please select a service', $response->getContent());
    }


    private function rowsToArray(Crawler $crawler)
    {
        $result = [];
        $rows = $crawler->filter('tr');
        $r = 0;
        for ($rowId = 0; $rowId <= $rows->count(); $rowId++) {
            $columns = $rows->eq($rowId)->filter('td');
            if (count($columns) > 0) {
                foreach ($columns as $columnId => $column) {
                    /** @var $column \DOMElement */
                    $result[$r][$columnId] = trim($column->textContent);
                }
                $r++;
            }
        }
        return $result;
    }
}
