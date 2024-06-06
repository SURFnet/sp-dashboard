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

class IdentityHeaderTest extends WebTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures();
    }

    public function test_page_header_displays_username()
    {
        $this->logIn();

        $crawler = self::$pantherClient->request('GET', '/service/create');

        $this->assertEquals(
            'John Doe Doe',
            trim($crawler->filter('a.welcome-link')->text())
        );
    }

    public function test_page_header_does_not_show_administration_links_to_services()
    {
        $ibuildings = $this->getServiceRepository()->findByName('Ibuildings B.V.');
        $this->logOut();
        $this->logIn($ibuildings);

        self::$pantherClient->request('GET', '/');

        self::assertNotOnPage('Add new service');
        self::assertNotOnPage('Edit service');
        self::assertNotOnPage('Translations');
    }

    public function test_page_header_displays_administration_links_to_administrators()
    {
        $this->logOut();
        $this->logIn();

        self::$pantherClient->request('GET', '/');

        self::assertOnPage('Add new service');
        self::assertOnPage('Translations');
    }
}
