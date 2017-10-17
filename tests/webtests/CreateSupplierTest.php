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
use Surfnet\ServiceProviderDashboard\Domain\Repository\SupplierRepository;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CreateSupplierTest extends WebTestCase
{
    /**
     * @var SupplierRepository
     */
    private $supplierRepository;

    public function setUp()
    {
        parent::setUp();

        $this->supplierRepository = $this->client->getContainer()->get('surfnet.dashboard.repository.supplier');
        $this->supplierRepository->clear();

        $supplier = m::mock(Supplier::class)->makePartial();
        $supplier->setName('test1');
        $supplier->setGuid('f1af6b9e-2546-4593-a57f-6ca34d2561e9');
        $supplier->setTeamName('team-test');
        $supplier->shouldReceive('getId')->andReturn(1);

        $this->supplierRepository->save($supplier);

        $this->client->getContainer()->get('surfnet.dashboard.service.authorization')->setAdminSwitcherSupplierId(1);
    }

    public function test_it_validates_the_form()
    {
        $this->logIn('ROLE_ADMINISTRATOR');

        $formData = [
            'dashboard_bundle_supplier_type' => [
                'guid' => 'a8a1fa6f-bffd-xxyz-874a-b9f4fdf92942',
                'name' => 'The A Team',
                'teamName' => 'team-a',
            ]
        ];

        $crawler = $this->client->request('GET', '/supplier/create');

        $form = $crawler
            ->selectButton('Save')
            ->form();

        $crawler = $this->client->submit($form, $formData);


        $nodes = $crawler->filter('#dashboard_bundle_supplier_type li');
        $this->assertEquals('This is not a valid UUID.', $nodes->first()->text());
    }

    public function test_it_rejects_duplicate_guids()
    {
        $this->logIn('ROLE_ADMINISTRATOR');

        $formData = [
            'dashboard_bundle_supplier_type' => [
                'guid' => 'f1af6b9e-2546-4593-a57f-6ca34d2561e9',
                'name' => 'The A Team',
                'teamName' => 'team-a',
            ]
        ];

        $crawler = $this->client->request('GET', '/supplier/create');

        $form = $crawler
            ->selectButton('Save')
            ->form();

        $crawler = $this->client->submit($form, $formData);

        $nodes = $crawler->filter('.page-container .message.error');

        $this->assertEquals(
            'The Guid of the Supplier should be unique. This Guid is taken by: "test1"',
            trim($nodes->first()->text())
        );
    }

    public function test_can_create_new_supplier()
    {
        $this->logIn('ROLE_ADMINISTRATOR');

        $formData = [
            'dashboard_bundle_supplier_type' => [
                'guid' => 'b9aaa8c4-3376-4e9d-b828-afa38cf29986',
                'name' => 'The A Team',
                'teamName' => 'team-a',
            ]
        ];

        $crawler = $this->client->request('GET', '/supplier/create');

        $form = $crawler
            ->selectButton('Save')
            ->form();

        $this->client->submit($form, $formData);

        $this->assertTrue(
            $this->client->getResponse() instanceof RedirectResponse,
            'Expecting a redirect response after adding a supplier'
        );

        $suppliers = $this->supplierRepository->findAll();
        $this->assertCount(2, $suppliers);

        $expectedUids = ['b9aaa8c4-3376-4e9d-b828-afa38cf29986', 'f1af6b9e-2546-4593-a57f-6ca34d2561e9'];
        foreach ($suppliers as $supplier) {
            $this->assertContains($supplier->getGuid(), $expectedUids);
        }
    }
}
