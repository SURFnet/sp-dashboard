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
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Protocol;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\RedirectResponse;

class EntityListTest extends WebTestCase
{
    public function test_entity_list_shows_entities()
    {
        $this->loadFixtures();
        $this->registerManageEntity(
            'test',
            'saml20_sp',
            '9729d851-cfdd-4283-a8f1-a29ba5036261',
            'SP1',
            'https://sp1-entityid.example.com',
            'https://sp1-entityid.example.com/metadata',
            'urn:collab:org:surf.nl'
        );
        $this->registerManageEntity(
            'test',
            'saml20_sp',
            '7398d851-abd1-2283-a8f1-a29ba5036174',
            'SP2',
            'https://sp2-entityid.example.com',
            'https://sp2-entityid.example.com/metadata',
            'urn:collab:org:surf.nl'
        );

        $this->logIn('ROLE_ADMINISTRATOR');
        $this->switchToService('SURFnet');

        $crawler = $this->client->request('GET', '/entities/1');

        $pageTitle = $crawler->filter('.page-container h1');

        $this->assertContains("Entities of service 'SURFnet'", $pageTitle->text());
        $data =  $this->rowsToArray($crawler->filter('table'));

        $this->assertCount(3, $data, 'Expecting three rows (including header)');

        unset($data[0][5]); // remove buttons
        $this->assertEquals([
            'SP1 Name English',
            'https://sp1-entityid.example.com',
            '',
            'saml20',
            'published',
        ], $data[0]);

        unset($data[1][5]); // remove buttons
        $this->assertEquals([
            'SP2 Name English',
            'https://sp2-entityid.example.com',
            '',
            'saml20',
            'published',
        ], $data[1]);

        $this->assertEquals([
            'There are no entities configured',
        ], $data[2]);
    }

    public function test_entity_list_shows_add_to_test_link()
    {
        $this->loadFixtures();

        // Surfnet is not allowed to create production entities.
        $service = $this->getServiceRepository()->findByName('SURFnet');
        $this->logIn('ROLE_USER', [$service]);

        $crawler = $this->client->request('GET', '/entities/1');
        $actions = $crawler->filter('a[href="#add-for-test"]');

        $this->assertContains('Add new entity', $actions->eq(0)->text(), 'Add for test link not found');
        $this->assertEquals(1, $actions->count(), 'There should be only one add link');
    }

    public function test_entity_list_shows_add_to_production_link()
    {
        $this->loadFixtures();

        // Ibuildings is allowed to create production entities.
        $service = $this->getServiceRepository()->findByName('Ibuildings B.V.');
        $this->logIn('ROLE_USER', [$service]);

        $crawler = $this->client->request('GET', '/entities/2');

        $actions = $crawler->filter('a[href="#add-for-production"]');

        $this->assertContains('Add new entity', $actions->eq(0)->text(), 'Add for production link not found');
    }

    private function rowsToArray(Crawler $crawler)
    {
        $result = [];
        $rows = $crawler->filter('tr');
        $r = 0;
        for ($rowId = 0; $rowId <= $rows->count(); $rowId++) {
            $columns = $rows->eq($rowId)->filter('td');
            if (count($columns) > 0) {
                foreach ($columns as $columnId => $column) {
                    /** @var $column \DOMElement */
                    $result[$r][$columnId] = trim($column->textContent);
                }
                $r++;
            }
        }
        return $result;
    }
}
