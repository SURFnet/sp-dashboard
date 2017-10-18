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

use Symfony\Component\HttpFoundation\RedirectResponse;

class ServiceListTest extends WebTestCase
{
    public function test_entity_list()
    {
        $this->loadFixtures();
        $this->logIn('ROLE_ADMINISTRATOR');

        $this->getAuthorizationService()->setAdminSwitcherSupplierId(
            $this->getSupplierRepository()->findByName('SURFnet')->getId()
        );

        $crawler = $this->client->request('GET', '/');

        $pageTitle = $crawler->filter('.page-container h1');

        $this->assertEquals('Services', $pageTitle->text());
        $this->assertCount(3, $crawler->filter('table tr'), 'Expecting three rows (including header)');

        $row = $crawler->filter('table tr')->eq(1);
        $this->assertEquals('SP1', $row->filter('td')->eq(0)->text(), 'Name not found in service list');
        $this->assertEquals('SP1', $row->filter('td')->eq(1)->text(), 'Entity ID not found in service list');
        $this->assertEquals('John Doe (jdoe@example.org)', $row->filter('td')->eq(2)->text(), 'Primary contact should be listed');
        $this->assertEquals('connect', $row->filter('td')->eq(3)->text(), 'Environment not found in service list');
    }

    public function test_entity_list_redirects_to_supplier_add_when_no_supplier_exists()
    {
        $this->clearFixtures();
        $this->logIn('ROLE_ADMINISTRATOR');

        $crawler = $this->client->request('GET', '/');
        $response = $this->client->getResponse();

        $this->assertTrue(
            $response instanceof RedirectResponse,
            'Expecting a redirect response to add form when no supplier exists'
        );

        $this->assertRegExp('#supplier/create$#', $response->headers->get('location'));
    }

    public function test_entity_list_shows_message_when_no_supplier_selected()
    {
        $this->loadFixtures();
        $this->logIn('ROLE_ADMINISTRATOR');

        $crawler = $this->client->request('GET', '/');
        $response = $this->client->getResponse();

        $this->assertContains('Please select a supplier', $response->getContent());
    }
}
