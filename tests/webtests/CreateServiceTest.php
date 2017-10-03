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

use Mockery as m;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Supplier;
use Surfnet\ServiceProviderDashboard\Domain\Repository\ServiceRepository;
use Surfnet\ServiceProviderDashboard\Domain\Repository\SupplierRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CreateServiceTest extends WebTestCase
{
    private $client;

    /**
     * @var SupplierRepository
     */
    private $supplierRepository;
    /**
     * @var ServiceRepository
     */
    private $serviceRepository;

    public function setUp()
    {
        $this->client = static::createClient();
        $this->supplierRepository = $this->client->getContainer()->get('surfnet.dashboard.repository.supplier');
        $this->supplierRepository->clear();

        $this->serviceRepository = $this->client->getContainer()->get('surfnet.dashboard.repository.service');
        $this->serviceRepository->clear();

        $supplier = m::mock(Supplier::class)->makePartial();
        $supplier->setName('test1');
        $supplier->shouldReceive('getId')->andReturn(1);

        $this->supplierRepository->save($supplier);

        $this->client->getContainer()->get('dashboard.service.admin_switcher')->setSelectedSupplier(1);
    }

    public function test_switcher_remembers_selected_supplier()
    {

        $this->client->request('GET', '/service/create');

        $this->assertTrue(
            $this->client->getResponse() instanceof RedirectResponse,
            'Expecting a redirect response after creating as Service'
        );

        $this->client->followRedirect();

        // One Service has been created
        $records = $this->serviceRepository->findAll();
        $this->assertCount(1, $records);
        /** @var Service $service */
        $service = $records[0];

        // The Id and TicketNumber fields are Uuids
        $this->assertNotEmpty($service->getId());
        $this->assertNotEmpty($service->getTicketNumber());

        $this->assertEquals(Service::ENVIRONMENT_CONNECT, $service->getEnvironment());
        $this->assertEquals(Service::STATE_DRAFT, $service->getStatus());
        $this->assertEquals('1', $service->getSupplier()->getId());
        $this->assertEquals('test1', $service->getSupplier()->getName());
    }
}
