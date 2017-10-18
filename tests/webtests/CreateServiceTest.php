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

use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CreateServiceTest extends WebTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->loadFixtures();

        $this->getAuthorizationService()->setAdminSwitcherSupplierId(
            $this->getSupplierRepository()->findByName('Ibuildings B.V.')->getId()
        );
    }

    public function test_switcher_remembers_selected_supplier()
    {
        $this->logIn('ROLE_ADMINISTRATOR');

        $this->client->request('GET', '/service/create');

        $this->assertTrue(
            $this->client->getResponse() instanceof RedirectResponse,
            'Expecting a redirect response after creating as Service'
        );

        $this->client->followRedirect();

        $supplier = $this->getSupplierRepository()->findByName('Ibuildings B.V.');
        $services = $supplier->getServices();

        // One Service has been created
        $this->assertCount(1, $services);

        /** @var Service $service */
        $service = $services->last();

        // The Id and TicketNumber fields are Uuids
        $this->assertNotEmpty($service->getId());

        $this->assertEquals(Service::ENVIRONMENT_CONNECT, $service->getEnvironment());
        $this->assertEquals(Service::STATE_DRAFT, $service->getStatus());
        $this->assertEquals('Ibuildings B.V.', $service->getSupplier()->getName());
    }
}
