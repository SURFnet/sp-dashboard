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

use Facebook\WebDriver\WebDriverBy;

/**
 * The service switcher was used for both the USER and ADMINISTRATOR roles. The users later stopped having this feature
 * in favour of a more user-friendly service overview page.
 */
class ServiceSwitcherTest extends WebTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function test_switcher_is_not_displayed_when_there_is_only_one_option()
    {
        $this->loadFixtures();

        $serviceRepository = $this->getServiceRepository();

        $this->logIn($serviceRepository->findByName('SURFnet'));

        $crawler = self::$pantherClient->request('GET', '/');

        $this->assertEmpty($crawler->filter('select#service-switcher'));
    }

    public function test_switcher_is_not_displayed_even_when_user_has_access_to_multiple_services()
    {
        $this->loadFixtures();

        $serviceRepository = $this->getServiceRepository();

        $this->logIn($serviceRepository->findByName('SURFnet'), $serviceRepository->findByName('Ibuildings B.V.'));

        $crawler = self::$pantherClient->request('GET', '/');

        $this->assertEmpty($crawler->filter('select#service-switcher'));
    }

    public function test_no_service_is_selected_when_session_is_empty()
    {
        $this->logIn();
        $this->loadFixtures();
        self::$pantherClient->request('GET', '/service/create');

        $this->assertEmpty(self::findBy('select#service-switcher')->getText());
    }

    public function test_switcher_lists_all_services_for_administrators()
    {
        $this->loadFixtures();
        $this->logIn();

        $crawler = self::$pantherClient->request('GET', '/service/create');

        $options = $crawler->filter('select#service-switcher option');
        $this->assertCount(4, $options, 'Expecting 3 services in service switcher (excluding empty option)');

        $crawler->filter('.service-switcher form')->click();
        $acme = "//li[contains(@id,'select2-service-switcher-result-')][1]";
        $ibuildings = "//li[contains(@id,'select2-service-switcher-result-')][2]";
        $surf = "//li[contains(@id,'select2-service-switcher-result-')][3]";

        $this->assertEquals('Acme Corporation [acme.com]', $crawler->findElement(WebDriverBy::xpath($acme))->getText());
        $this->assertEquals('Ibuildings B.V. [ibuildings.nl]', $crawler->findElement(WebDriverBy::xpath($ibuildings))->getText());
        $this->assertEquals('SURFnet [surf.nl]', $crawler->findElement(WebDriverBy::xpath($surf))->getText());
    }
}
