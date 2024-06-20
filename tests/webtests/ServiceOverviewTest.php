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

use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\DataFixtures\ORM\WebTestFixtures;

class ServiceOverviewTest extends WebTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures();
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

        $this->logIn($surfNet);
        $crawler = self::$pantherClient->request('GET', '/');

        // By retrieving the h1 titles (stating the services) we can conclude if the correct data is displayed.
        $h1 = $crawler->filter('.service-title');
        $this->assertStringContainsString('My services', $h1->first()->text());
        $h2 = $crawler->filter('.service-status-title');

        $this->assertStringContainsString('SURFnet', trim($h2->text()));
    }

    /**
     * Multiple services can be listed on the page
     */
    public function test_multiple_services_can_be_listed()
    {
        $serviceRepository = $this->getServiceRepository();
        $surfNet = $serviceRepository->findByName('SURFnet');
        $ibuildings = $serviceRepository->findByName('Ibuildings B.V.');

        $this->logIn($surfNet, $ibuildings);

        $crawler = self::$pantherClient->request('GET', '/');

        // By retrieving the h2 titles (stating the services) we can conclude if the correct data is displayed.
        $nodes = $crawler->filter('.service-status-title');
        // Two services should be on page: surf and ibuildings.
        $this->assertEquals(2, $nodes->count());

        // The two nodes are sorted alphabetically.
        $serviceNode = $nodes->first();
        $service2 = $nodes->eq(1);

        $this->assertStringContainsString('Ibuildings B.V.', trim($serviceNode->text()));
        $this->assertStringContainsString('SURFnet', trim($service2->text()));
    }

    /**
     * Entities of a service are listed on the page
     */
    public function test_entitites_of_a_service_are_listed()
    {
        $this->registerManageEntity(
            'test',
            'saml20_sp',
            '9729d851-cfdd-4283-a8f1-a29ba5036261',
            'SP1',
            'https://sp1-entityid.example.com',
            'https://sp1-entityid.example.com/metadata',
            WebTestFixtures::TEAMNAME_SURF
        );
        $this->registerManageEntity(
            'test',
            'saml20_sp',
            '7398d851-abd1-2283-a8f1-a29ba5036174',
            'SP2',
            'https://sp2-entityid.example.com',
            'https://sp2-entityid.example.com/metadata',
            WebTestFixtures::TEAMNAME_SURF
        );

        $serviceRepository = $this->getServiceRepository();
        $surfNet = $serviceRepository->findByName('SURFnet');
        $ibuildings = $serviceRepository->findByName('Ibuildings B.V.');
        $this->logIn($surfNet, $ibuildings);

        $crawler = self::$pantherClient->request('GET', '/');

        // By retrieving the h2 titles (stating the services) we can conclude if the correct data is displayed.
        $nodes = $crawler->filter('.service-status-container');

        // Two services should be on page: surf and ibuildings.
        $this->assertEquals(2, $nodes->count());

        $Ibuildings = $nodes->first();
        $SURF = $nodes->eq(1);

        $productionTableIbuildings = $Ibuildings->filter('.service-status-entities-table.production-entities');
        $this->assertStringContainsString(
            'Production entities',
            $productionTableIbuildings->filter('.service-status-entities-table-title')->text()
        );
        $productionNoEntitiesIbuildings = $Ibuildings->filter('.no-entities-production')->text();
        $this->assertEquals('No entities found.', $productionNoEntitiesIbuildings);

        $testTableIbuildings = $Ibuildings->filter('.service-status-entities-table.test-entities');
        $this->assertStringContainsString(
            'Test entities',
            $testTableIbuildings->filter('.service-status-entities-table-title')->text()
        );
        $testNoEntitiesIbuildings = $Ibuildings->filter('.no-entities-test')->text();
        $this->assertEquals('No entities found.', $testNoEntitiesIbuildings);

        $productionTableSurf = $SURF->filter('.service-status-entities-table.production-entities');
        $this->assertStringContainsString(
            'Production entities',
            $productionTableSurf->filter('.service-status-entities-table-title')->text()
        );
        $productionNoEntitiesSurf = $SURF->filter('.no-entities-production')->text();
        $this->assertEquals('No entities found.', $productionNoEntitiesSurf);

        $testTableSurf = $SURF->filter('.service-status-entities-table.test-entities');
        $this->assertStringContainsString('Test entities', $testTableSurf->filter('.service-status-entities-table-title')->text());
        $actionsRow = "ViewEditEditIdPwhitelistDelete";
        $this->assertStringContainsString("SP1 Name English", $testTableSurf->text());
        $this->assertStringContainsString("SP2 Name English", $testTableSurf->text());
    }

    public function test_service_overview_redirects_to_service_add_when_no_service_exists()
    {
        $this->clearFixtures();
        $this->logOut();
        $this->logIn();

        self::$pantherClient->request('GET', '/');
        self::assertOnPage('Add new service');
    }

    public function test_service_overview_shows_message_when_no_service_selected()
    {
        $this->loadFixtures();
        $this->logOut();
        $this->logIn();

        self::$pantherClient->request('GET', '/');

        self::assertOnPage('Please use the service switcher to manage the entities of one of the services.');
    }

    public function test_entity_list_shows_add_to_test_link()
    {
        $service = $this->getServiceRepository()->findByName('SURFnet');
        $this->logIn($service);
        $this->loadFixtures();
        $crawler = self::$pantherClient->request('GET', '/');
        // Verify the checkbox is on the page (used to trigger the modal when clicking the link)
        $testCheckbox = $crawler->filter('#add-for-test-SURFnet');

        $this->assertNotEmpty($testCheckbox, 'Add for test link not found');
    }

    public function test_entity_list_shows_add_to_production_link()
    {
        $this->loadFixtures();
        // Ibuildings is allowed to create production entities.
        $service = $this->getServiceRepository()->findByName('Ibuildings B.V.');
        $this->logIn($service);
        $crawler = self::$pantherClient->request('GET', '/');
        // Verify the link is on the page to trigger the modal window
        $actions = $crawler->filter('.link[for^="add-for-production-Ibuildings-B.V."]');

        $this->assertStringContainsString('New production entity', $actions->eq(0)->text(), 'Add for production link not found');
    }
}
