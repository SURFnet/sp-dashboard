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
use Surfnet\ServiceProviderDashboard\Domain\Entity\Supplier;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AdminSwitcherTest extends WebTestCase
{
    /**
     * @var Surfnet\ServiceProviderDashboard\WebTests\Repository\InMemorySupplierRepository
     */
    private $repository;

    public function setUp()
    {
        parent::setUp();

        $this->repository = $this->client->getContainer()->get('surfnet.dashboard.repository.supplier');
        $this->repository->clear();

        $supplier1 = m::mock(Supplier::class)->makePartial();
        $supplier1->setName('test1');
        $supplier1->shouldReceive('getId')->andReturn('test1');

        $supplier2 = m::mock(Supplier::class)->makePartial();
        $supplier2->setName('test2');
        $supplier2->shouldReceive('getId')->andReturn('test2');

        $this->repository->save($supplier1);
        $this->repository->save($supplier2);
    }

    public function test_no_supplier_is_selected_when_session_is_empty()
    {
        $this->logIn('ROLE_ADMINISTRATOR');

        $crawler = $this->client->request('GET', '/supplier/create');

        $this->assertEmpty(
            $crawler->filter('select#admin-switcher option:selected')
        );
    }

    public function test_switcher_lists_all_suppliers()
    {
        $this->logIn('ROLE_ADMINISTRATOR');

        $crawler = $this->client->request('GET', '/supplier/create');
        $options = $crawler->filter('select#admin-switcher option');

        $this->assertCount(3, $options, 'Expecting 2 suppliers in admin switcher (excluding empty option)');

        $this->assertEquals('', $options->eq(0)->text());
        $this->assertEquals('test1', $options->eq(1)->text());
        $this->assertEquals('test2', $options->eq(2)->text());
    }

    public function test_switcher_remembers_selected_supplier()
    {
        $this->logIn('ROLE_ADMINISTRATOR');

        $crawler = $this->client->request('GET', '/supplier/create');
        $form = $crawler->filter('.admin-switcher')
            ->selectButton('Select')
            ->form();

        $form['supplier']->select('test2');

        $this->client->submit($form);

        $this->assertTrue(
            $this->client->getResponse() instanceof RedirectResponse,
            'Expecting a redirect response after selecting a supplier'
        );

        $crawler = $this->client->followRedirect();
        $selectedSupplier = $crawler->filter('select#admin-switcher option:selected')->first();

        $this->assertEquals('test2', $selectedSupplier->text(), "Suplier 'test2' should be selected");
    }
}
