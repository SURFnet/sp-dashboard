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

class ServiceAdminOverviewTest extends WebTestCase
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
     * Entities of a service are listed on the page
     */
    public function test_only_one_service_is_displayed()
    {
        $serviceRepository = $this->getServiceRepository();
        $surfNet = $serviceRepository->findByName('SURFnet');
        $ibuildings = $serviceRepository->findByName('Ibuildings B.V.');

        $this->testMockHandler->append(new Response(200, [], '[]'));
        $this->testMockHandler->append(new Response(200, [], '[]'));
        $this->prodMockHandler->append(new Response(200, [], '[]'));
        $this->prodMockHandler->append(new Response(200, [], '[]'));

        $this->logIn('ROLE_ADMINISTRATOR', [$surfNet, $ibuildings]);

        $crawler = $this->client->request('GET', '/service/2');

        // By retrieving the h1 titles (stating the services) we can conclude if the correct data is displayed.
        $h1 = $crawler->filter('.page-container h1');
        $this->assertEquals('Service overview', $h1->first()->text());

        $nodes = $crawler->filter('.service-status-container .service-status-title a');

        // One service should be on page: ibuildings
        $this->assertEquals(1, $nodes->count());
        $this->assertEquals('Ibuildings B.V.', $nodes->first()->text());
    }
}
