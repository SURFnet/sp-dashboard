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

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CreateSupplierTest extends WebTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->loadFixtures();

        $this->getAuthorizationService()->setAdminSwitcherSupplierId(
            $this->getSupplierRepository()->findByName('SURFnet')->getId()
        );
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

        $existingGuid = $this->getSupplierRepository()->findByName('SURFnet')->getGuid();

        $formData = [
            'dashboard_bundle_supplier_type' => [
                'guid' => $existingGuid,
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
            'The Guid of the Supplier should be unique. This Guid is taken by: "SURFnet"',
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

        $suppliers = $this->getSupplierRepository()->findAll();
        $this->assertCount(3, $suppliers);
    }
}
