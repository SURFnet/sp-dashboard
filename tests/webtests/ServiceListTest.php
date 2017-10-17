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
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Contact;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ServiceListTest extends WebTestCase
{
    /**
     * @var \Surfnet\ServiceProviderDashboard\Webtests\Repository\InMemorySupplierRepository
     */
    private $suppliers;

    /**
     * @var \Surfnet\ServiceProviderDashboard\Webtests\Repository\InMemoryServiceRepository
     */
    private $services;

    public function setUp()
    {
        parent::setUp();

        $this->suppliers = $this->client->getContainer()->get('surfnet.dashboard.repository.supplier');
        $this->services = $this->client->getContainer()->get('surfnet.dashboard.repository.service');
        $this->suppliers->clear();
        $this->services->clear();

        $supplier1 = m::mock(Supplier::class)->makePartial();
        $supplier1->setName('test1');
        $supplier1->shouldReceive('getId')->andReturn('test1');

        $supplier2 = m::mock(Supplier::class)->makePartial();
        $supplier2->setName('test2');
        $supplier2->shouldReceive('getId')->andReturn('test2');

        $this->suppliers->save($supplier1);
        $this->suppliers->save($supplier2);

        $contact = new Contact();
        $contact->setFirstName('John');
        $contact->setLastName('Doe');
        $contact->setEmail('jdoe@example.org');

        $service1 = new Service();
        $service1->setId(1);
        $service1->setSupplier($supplier1);
        $service1->setNameEn('Service1');
        $service1->setEntityId('service-1');
        $service1->setEnvironment('connect');
        $service1->setAdministrativeContact($contact);

        $service2 = new Service();
        $service2->setId(2);
        $service2->setSupplier($supplier1);
        $service2->setNameEn('Service2');
        $service2->setEntityId('service-2');
        $service2->setEnvironment('connect');
        $service2->setAdministrativeContact($contact);

        $this->services->save($service1);
        $this->services->save($service2);
    }

    public function test_entity_list()
    {
        $this->logIn('ROLE_ADMINISTRATOR');

        $this->client->getContainer()->get('surfnet.dashboard.service.authorization')
            ->setAdminSwitcherSupplierId('test1');

        $crawler = $this->client->request('GET', '/');

        $pageTitle = $crawler->filter('.page-container h1');

        $this->assertEquals('Services', $pageTitle->text());
        $this->assertCount(3, $crawler->filter('table tr'), 'Expecting three rows (including header)');

        $row = $crawler->filter('table tr')->eq(1);
        $this->assertEquals('Service1', $row->filter('td')->eq(0)->text(), 'Name not found in service list');
        $this->assertEquals('service-1', $row->filter('td')->eq(1)->text(), 'Entity ID not found in service list');
        $this->assertEquals('John Doe (jdoe@example.org)', $row->filter('td')->eq(2)->text(), 'Primary contact should be listed');
        $this->assertEquals('connect', $row->filter('td')->eq(3)->text(), 'Environment not found in service list');
    }

    public function test_entity_list_redirects_to_supplier_add_when_no_supplier_exists()
    {
        $this->logIn('ROLE_ADMINISTRATOR');

        $this->suppliers->clear();

        $crawler = $this->client->request('GET', '/');
        $response = $this->client->getResponse();

        $this->assertTrue(
            $response instanceof RedirectResponse,
            'Expecting a redirect response after selecting a supplier'
        );

        $this->assertRegExp('#supplier/create$#', $response->headers->get('location'));
    }

    public function test_entity_list_shows_message_when_no_supplier_selected()
    {
        $this->logIn('ROLE_ADMINISTRATOR');

        $crawler = $this->client->request('GET', '/');
        $response = $this->client->getResponse();

        $this->assertContains('Please select a supplier', $response->getContent());
    }
}
