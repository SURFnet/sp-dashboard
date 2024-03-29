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

class ServiceAdminOverviewTest extends WebTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures();
        $this->logIn();
    }

    /**
     * Entities of a service are listed on the page
     */
    public function test_only_one_service_is_displayed()
    {
        $crawler = self::$pantherClient->request('GET', '/service/2');

        // By retrieving the h1 titles (stating the services) we can conclude if the correct data is displayed.
        $h1 = $crawler->filter('.page-container .service-title');
        $this->assertEquals('Ibuildings B.V. overview', trim($h1->first()->text()));

        $nodes = $crawler->filter('.service-status-container .service-status-title');

        // One service should be on page: ibuildings
        $this->assertEquals(1, $nodes->count());
        $this->assertStringContainsString('Ibuildings B.V.', $nodes->first()->text());
    }
}
