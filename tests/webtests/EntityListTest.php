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
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\RedirectResponse;

class EntityListTest extends WebTestCase
{
    public function test_entity_list_shows_draft_entities()
    {
        $this->loadFixtures();
        $this->logIn('ROLE_ADMINISTRATOR');

        $this->testMockHandler->append(new Response(200, [], '[]'));
        $this->prodMockHandler->append(new Response(200, [], '[]'));

        $this->getAuthorizationService()->setSelectedServiceId(
            $this->getServiceRepository()->findByName('SURFnet')->getId()
        );

        $crawler = $this->client->request('GET', '/entities/1');

        $pageTitle = $crawler->filter('.page-container h1');

        $this->assertContains("Entities of service 'SURFnet'", $pageTitle->text());
        $data =  $this->rowsToArray($crawler->filter('table'));

        $this->assertCount(3, $data, 'Expecting three rows (including header)');

        unset($data[0][5]); // remove buttons
        $this->assertEquals([
            'SP1',
            'SP1',
            'John Doe (jdoe@example.org)',
            'saml20',
            'draft',
        ], $data[0]);

        unset($data[1][5]); // remove buttons
        $this->assertEquals([
            'SP2',
            'SP2',
            'John Doe (jdoe@example.org)',
            'saml20',
            'draft',
        ], $data[1]);

        $this->assertEquals([
            'There are no entities configured',
        ], $data[2]);
    }

    public function test_entity_list_shows_test_entities()
    {
        $this->loadFixtures();
        $this->logIn('ROLE_ADMINISTRATOR');

        $searchResponse = json_encode([
            (object)[
                '_id' => '9729d851-cfdd-4283-a8f1-a29ba5036261',
            ],
        ]);

        $sp3QueryResponse = json_encode((object)[
            'id' => '9729d851-cfdd-4283-a8f1-a29ba5036261',
            'data' => (object)[
                'entityid' => 'SP3',
                'metaDataFields' => (object) [
                    'name:en' => 'SP3',
                    'contacts:0:contactType' => 'administrative',
                    'contacts:0:givenName' => 'Test',
                    'contacts:0:surName' => 'Test',
                    'contacts:0:emailAddress' => 'test@example.org',
                ],
            ],
        ]);

        $this->testMockHandler->append(new Response(200, [], $searchResponse));
        $this->testMockHandler->append(new Response(200, [], $sp3QueryResponse));
        $this->prodMockHandler->append(new Response(200, [], '[]'));

        $this->getAuthorizationService()->setSelectedServiceId(
            $this->getServiceRepository()->findByName('SURFnet')->getId()
        );

        $crawler = $this->client->request('GET', '/entities/1');

        $pageTitle = $crawler->filter('.page-container h1');

        $this->assertContains("Entities of service 'SURFnet'", $pageTitle->text());
        $data =  $this->rowsToArray($crawler->filter('table'));

        $this->assertCount(4, $data, 'Expecting three rows (including header)');

        unset($data[0][5]); // remove buttons
        $this->assertEquals([
            'SP1',
            'SP1',
            'John Doe (jdoe@example.org)',
            'saml20',
            'draft',
        ], $data[0]);

        unset($data[1][5]); // remove buttons
        $this->assertEquals([
            'SP2',
            'SP2',
            'John Doe (jdoe@example.org)',
            'saml20',
            'draft',
        ], $data[1]);

        unset($data[2][5]); // remove buttons
        $this->assertEquals([
            'SP3',
            'SP3',
            'Test Test (test@example.org)',
            'saml20',
            'published',
        ], $data[2]);

        $this->assertEquals([
            'There are no entities configured',
        ], $data[3]);
    }

    public function test_entity_list_shows_add_to_test_link()
    {
        $this->loadFixtures();

        // Surfnet is not allowed to create production entities.
        $service = $this->getServiceRepository()->findByName('SURFnet');
        $this->logIn('ROLE_USER', [$service]);

        $this->testMockHandler->append(new Response(200, [], '[]'));
        $this->prodMockHandler->append(new Response(200, [], '[]'));

        $this->getAuthorizationService()->setSelectedServiceId(
            $service->getId()
        );

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

        $this->testMockHandler->append(new Response(200, [], '[]'));
        $this->prodMockHandler->append(new Response(200, [], '[]'));

        $this->getAuthorizationService()->setSelectedServiceId(
            $service->getId()
        );

        $crawler = $this->client->request('GET', '/entities/2');

        $actions = $crawler->filter('a[href="#add-for-production"]');

        $this->assertContains('Add new entity', $actions->eq(0)->text(), 'Add for production link not found');
    }

    public function test_create_entity_buttons_trigger_the_entity_type_dialog()
    {
        $this->loadFixtures();
        $service = $this->getServiceRepository()->findByName('Ibuildings B.V.');
        $this->logIn('ROLE_USER', [$service]);

        // The entity overview page is loaded twice, so manage is asked twice for getting prod & test entities.
        $this->testMockHandler->append(new Response(200, [], '[]'));
        $this->prodMockHandler->append(new Response(200, [], '[]'));
        $this->testMockHandler->append(new Response(200, [], '[]'));
        $this->prodMockHandler->append(new Response(200, [], '[]'));

        $this->getAuthorizationService()->setSelectedServiceId(
            $service->getId()
        );

        $crawler = $this->client->request('GET', '/entities/2');

        // Assert the two modal windows are on the page and have a form with appropriate form actions.
        $modalTest = $crawler->filter('#add-for-test form');
        $modalProd = $crawler->filter('#add-for-production form');
        $testAction = $modalTest->first()->attr('action');
        $prodAction = $modalProd->first()->attr('action');

        $this->assertEquals('/entity/create/type/2', $testAction);
        $this->assertEquals('/entity/create/type/2/production', $prodAction);

        // Now submit one of the forms and ascertain we ended up on the edit entity form
        $form = $crawler->filter('#add-for-test')
            ->selectButton('Create')
            ->form();
        $this->client->submit($form);
        $this->assertTrue(
            $this->client->getResponse() instanceof RedirectResponse,
            'Expected a redirect to the /entity/create/type action'
        );

        $this->assertRegExp('/\/entity\/create\/type\/2/', $this->client->getRequest()->getRequestUri());

        // TODO: test submitting to the openidconnect form that is yet to be created
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
