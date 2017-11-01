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
use Symfony\Component\HttpFoundation\RedirectResponse;

class EntityListTest extends WebTestCase
{
    public function test_entity_list_shows_draft_entities()
    {
        $this->loadFixtures();
        $this->logIn('ROLE_ADMINISTRATOR');

        $this->mockHandler->append(new Response(200, [], '[]'));

        $this->getAuthorizationService()->setSelectedServiceId(
            $this->getServiceRepository()->findByName('SURFnet')->getId()
        );

        $crawler = $this->client->request('GET', '/');

        $pageTitle = $crawler->filter('.page-container h1');

        $this->assertEquals('My entities', $pageTitle->text());
        $this->assertCount(3, $crawler->filter('table tr'), 'Expecting three rows (including header)');

        $row = $crawler->filter('table tr')->eq(1);
        $this->assertEquals('SP1', $row->filter('td')->eq(0)->text(), 'Name not found in entity list');
        $this->assertEquals('SP1', $row->filter('td')->eq(1)->text(), 'Entity ID not found in entity list');
        $this->assertEquals('John Doe (jdoe@example.org)', $row->filter('td')->eq(2)->text(), 'Primary contact should be listed');
        $this->assertEquals('draft', $row->filter('td')->eq(3)->text(), 'State not found in entity list');
        $this->assertEquals('connect', $row->filter('td')->eq(4)->text(), 'Environment not found in entity list');

        $row = $crawler->filter('table tr')->eq(2);
        $this->assertEquals('SP2', $row->filter('td')->eq(0)->text(), 'Name not found in entity list');
        $this->assertEquals('SP2', $row->filter('td')->eq(1)->text(), 'Entity ID not found in entity list');
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

        $this->mockHandler->append(new Response(200, [], $searchResponse));
        $this->mockHandler->append(new Response(200, [], $sp3QueryResponse));

        $this->getAuthorizationService()->setSelectedServiceId(
            $this->getServiceRepository()->findByName('SURFnet')->getId()
        );

        $crawler = $this->client->request('GET', '/');

        $pageTitle = $crawler->filter('.page-container h1');

        $this->assertEquals('My entities', $pageTitle->text());
        $this->assertCount(4, $crawler->filter('table tr'), 'Expecting three rows (including header)');

        $row = $crawler->filter('table tr')->eq(3);
        $this->assertEquals('SP3', $row->filter('td')->eq(0)->text(), 'Name not found in entity list');
        $this->assertEquals('SP3', $row->filter('td')->eq(1)->text(), 'Entity ID not found in entity list');
        $this->assertEquals('Test Test (test@example.org)', $row->filter('td')->eq(2)->text(), 'Primary contact should be listed');
        $this->assertEquals('published', $row->filter('td')->eq(3)->text(), 'State not found in entity list');
        $this->assertEquals('connect', $row->filter('td')->eq(4)->text(), 'Environment not found in entity list');
    }

    public function test_entity_list_redirects_to_service_add_when_no_service_exists()
    {
        $this->clearFixtures();
        $this->logIn('ROLE_ADMINISTRATOR');

        $crawler = $this->client->request('GET', '/');
        $response = $this->client->getResponse();

        $this->assertTrue(
            $response instanceof RedirectResponse,
            'Expecting a redirect response to add form when no service exists'
        );

        $this->assertRegExp('#service/create$#', $response->headers->get('location'));
    }

    public function test_entity_list_shows_message_when_no_service_selected()
    {
        $this->loadFixtures();
        $this->logIn('ROLE_ADMINISTRATOR');

        $crawler = $this->client->request('GET', '/');
        $response = $this->client->getResponse();

        $this->assertContains('Please select a service', $response->getContent());
    }
}
