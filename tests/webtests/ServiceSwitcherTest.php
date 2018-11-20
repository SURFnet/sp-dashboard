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
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * The service switcher was used for both the USER and ADMINISTRATOR roles. The users later stopped having this feature
 * in favour of a more user friendly service overview page.
 */
class ServiceSwitcherTest extends WebTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->testMockHandler->append(new Response(200, [], '[]'));
        $this->prodMockHandler->append(new Response(200, [], '[]'));
    }

    public function test_switcher_is_not_displayed_when_there_is_only_one_option()
    {
        $this->loadFixtures();

        $serviceRepository = $this->getServiceRepository();

        $this->logIn(
            'ROLE_USER',
            [
                $serviceRepository->findByName('SURFnet'),
            ]
        );

        $crawler = $this->client->request('GET', '/');

        $this->assertEmpty($crawler->filter('select#service-switcher'));
    }

    public function test_switcher_is_not_displayed_even_when_user_has_access_to_multiple_services()
    {
        $this->loadFixtures();

        $serviceRepository = $this->getServiceRepository();

        $this->logIn(
            'ROLE_USER',
            [
                $serviceRepository->findByName('SURFnet'),
                $serviceRepository->findByName('Ibuildings B.V.'),
            ]
        );

        $crawler = $this->client->request('GET', '/');

        $this->assertEmpty($crawler->filter('select#service-switcher'));
    }

    public function test_no_service_is_selected_when_session_is_empty()
    {
        $this->logIn('ROLE_ADMINISTRATOR');
        $this->loadFixtures();

        $crawler = $this->client->request('GET', '/service/create');

        $this->assertEmpty(
            $crawler->filter('select#service-switcher option:selected')
        );
    }

    public function test_switcher_lists_all_services_for_administrators()
    {
        $this->logIn('ROLE_ADMINISTRATOR');
        $this->loadFixtures();

        $crawler = $this->client->request('GET', '/service/create');
        $options = $crawler->filter('select#service-switcher option');

        $this->assertCount(3, $options, 'Expecting 2 services in service switcher (excluding empty option)');

        $this->assertEquals('', $options->eq(0)->text());
        $this->assertEquals('Ibuildings B.V.', $options->eq(1)->text());
        $this->assertEquals('SURFnet', $options->eq(2)->text());
    }

    public function test_switcher_remembers_selected_services()
    {
        $this->logIn('ROLE_ADMINISTRATOR');
        $this->loadFixtures();

        $crawler = $this->client->request('GET', '/service/create');
        $form = $crawler->filter('.service-switcher')
            ->selectButton('Select')
            ->form();

        $form['service']->select(
            $this->getServiceRepository()->findByName('SURFnet')->getId()
        );

        $this->client->submit($form);

        $this->assertTrue(
            $this->client->getResponse() instanceof RedirectResponse,
            'Expecting a redirect response after selecting a service'
        );

        $crawler = $this->client->followRedirect();

        $selectedService = $crawler->filter('select#service-switcher option:selected')->first();

        $this->assertEquals('SURFnet', $selectedService->text(), "Service 'SURFnet' should be selected");
    }
}
