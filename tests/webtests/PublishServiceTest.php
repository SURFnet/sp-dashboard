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
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Contact;
use Surfnet\ServiceProviderDashboard\Domain\Repository\ServiceRepository;
use Surfnet\ServiceProviderDashboard\Domain\Repository\SupplierRepository;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;

class EditServiceTest extends WebTestCase
{
    /**
     * @var Client
     */
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

        $supplier = m::mock(Supplier::class)->makePartial();
        $supplier->setName('test1');
        $supplier->setGuid('f1af6b9e-2546-4593-a57f-6ca34d2561e9');
        $supplier->setTeamName('team-test');
        $supplier->shouldReceive('getId')->andReturn(1);

        $this->supplierRepository->save($supplier);

        $this->serviceRepository = $this->client->getContainer()->get('surfnet.dashboard.repository.service');
        $this->serviceRepository->clear();

        $service = new Service();
        $service->setId('a8e7cffd-0409-45c7-a37a-81bb5e7e5f66');
        $service->setStatus(Service::STATE_DRAFT);
        $service->setSupplier($supplier);
        $service->setNameEn('MyEntity');
        $service->setTicketNumber('IID-9');
        $service->setMetadataXml(file_get_contents(__DIR__ . '/fixtures/publish/metadata.xml'));

        $this->serviceRepository->save($service);

        $this->client->getContainer()->get('surfnet.dashboard.service.admin_switcher')->setSelectedSupplier(1);
    }

    public function test_it_published_metadata_to_manage()
    {
        $crawler = $this->client->request('GET', '/service/edit/a8e7cffd-0409-45c7-a37a-81bb5e7e5f66');

        $form = $crawler
            ->selectButton('Publish')
            ->form();
        $this->client->submit($form);

        $this->assertTrue(true);
    }
}
