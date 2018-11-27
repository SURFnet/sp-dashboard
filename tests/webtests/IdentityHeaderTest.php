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

class IdentityHeaderTest extends WebTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->loadFixtures();
    }

    public function test_page_header_displays_username()
    {
        $this->logIn('ROLE_ADMINISTRATOR');

        $crawler = $this->client->request('GET', '/service/create');

        $this->assertEquals(
            'John Doe',
            trim($crawler->filter('a.welcome-link')->text())
        );
    }

    public function test_page_header_does_not_show_administration_links_to_services()
    {
        $this->logIn('ROLE_USER');

        $crawler = $this->client->request('GET', '/');

        $this->assertEmpty($crawler->filter('.navigation ul li:contains("Add new service")'));
        $this->assertEmpty($crawler->filter('.navigation ul li:contains("Edit service")'));
        $this->assertEmpty($crawler->filter('.navigation ul li:contains("Translations")'));
    }

    public function test_page_header_displays_administration_links_to_administrators()
    {
        $this->testMockHandler->append(new Response(200, [], '[]'));
        $this->testMockHandler->append(new Response(200, [], '[]'));
        $this->prodMockHandler->append(new Response(200, [], '[]'));
        $this->prodMockHandler->append(new Response(200, [], '[]'));

        $this->logIn('ROLE_ADMINISTRATOR');

        $crawler = $this->client->request('GET', '/');

        $this->assertCount(1, $crawler->filter('.navigation ul li:contains("Add new service")'));
        $this->assertCount(1, $crawler->filter('.navigation ul li:contains("Translations")'));
    }
}
