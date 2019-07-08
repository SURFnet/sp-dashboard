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

class EntityDeleteTest extends WebTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->loadFixtures();
        $this->logIn('ROLE_ADMINISTRATOR');

        $this->service = $this->getServiceRepository()->findByName('SURFnet');

        $this->getAuthorizationService()->setSelectedServiceId($this->service->getId());
    }

    public function test_a_normal_user_can_not_delete_entities()
    {
        $this->logIn('ROLE_USER');

        $entity = $this->service->getEntities()->first();

        $this->client->request('GET', "/entity/delete/{$entity->getId()}");

        $statusCode = $this->client->getResponse()->getStatusCode();

        $this->assertSame(403, $statusCode);
    }

    public function test_delete_returns_to_entity_list()
    {
        $entity = $this->service->getEntities()->first();

        $crawler = $this->client->request('GET', "/entity/delete/{$entity->getId()}");

        $pageTitle = $crawler->filter('.page-container h1');

        $this->assertEquals('Delete entity', $pageTitle->text());

        $form = $crawler->filter('.page-container')
            ->selectButton('Delete')
            ->form();

        $this->client->submit($form);

        $this->assertTrue(
            $this->client->getResponse() instanceof RedirectResponse,
            'Expecting a redirect response after editing an entity'
        );

        $crawler = $this->client->followRedirect();

        $row = $crawler->filter('table tr')->eq(1);

        $this->assertNotContains($entity->getNameEn(), $row->text());
    }

    public function test_cancel_returns_to_entity_list()
    {
        $entity = $this->service->getEntities()->first();

        $crawler = $this->client->request('GET', "/entity/delete/{$entity->getId()}");

        $pageTitle = $crawler->filter('.page-container h1');

        $this->assertEquals('Delete entity', $pageTitle->text());

        $form = $crawler->filter('.page-container')
            ->selectButton('Cancel')
            ->form();

        $this->client->submit($form);

        $this->assertTrue(
            $this->client->getResponse() instanceof RedirectResponse,
            'Expecting a redirect response after editing an entity'
        );

        $this->testMockHandler->append(new Response(200, [], '[]'));
        $this->prodMockHandler->append(new Response(200, [], '[]'));

        $crawler = $this->client->followRedirect();

        $row = $crawler->filter('table tr')->eq(1);

        $this->assertContains($entity->getNameEn(), $row->text(), 'The entity should not have been deleted after cancel');
    }

    public function test_delete_a_published_test_entity()
    {
        $queryResponse = json_encode((object)[
            'id' => 'a8e7cffd-0409-45c7-a37a-000000000000',
            'data' => (object)[
                'entityid' => 'SP1',
                'metaDataFields' => (object) [
                    'name:en' => 'SP1',
                    'contacts:0:contactType' => 'administrative',
                    'contacts:0:givenName' => 'Test',
                    'contacts:0:surName' => 'Test',
                    'contacts:0:emailAddress' => 'test@example.org',
                    'coin:service_team_id' => 'urn:collab:org:surf.nl',
                ],
            ],
        ]);

        // Authz test (ManageEntityAccessGrantedVoter)
        $this->testMockHandler->append(new Response(200, [], $queryResponse));
        // Rendering the form requires retrieval of the manage entity
        $this->testMockHandler->append(new Response(200, [], $queryResponse));
        // Successfull deleting an entity from manage results in return type boolean : true
        $this->testMockHandler->append(new Response(200, [], json_encode(true)));

        $crawler = $this->client->request('GET', "/entity/delete/published/1/a8e7cffd-0409-45c7-a37a-000000000000");

        $pageTitle = $crawler->filter('.page-container h1');

        $this->assertEquals('Delete entity', $pageTitle->text());

        $form = $crawler->filter('.page-container')
            ->selectButton('Delete')
            ->form();

        $this->client->submit($form);

        $response = $this->client->getResponse();
        $this->assertTrue(
            $response instanceof RedirectResponse,
            'Expecting a redirect response after editing an entity'
        );
    }

    public function test_delete_a_unpublished_production_entity()
    {
        $queryResponse = json_encode((object)[
            'id' => 'a8e7cffd-0409-45c7-a37a-000000000000',
            'data' => (object)[
                'entityid' => 'SP1',
                'metaDataFields' => (object) [
                    'name:en' => 'SP1',
                    'contacts:0:contactType' => 'administrative',
                    'contacts:0:givenName' => 'Test',
                    'contacts:0:surName' => 'Test',
                    'contacts:0:emailAddress' => 'test@example.org',
                    'coin:service_team_id' => 'urn:collab:org:surf.nl',
                ],
            ],
        ]);

        // Authz test (ManageEntityAccessGrantedVoter) (tested twice for both controller entries)
        $this->prodMockHandler->append(new Response(200, [], $queryResponse));

        // Rendering the form requires retrieval of the manage entity
        $this->prodMockHandler->append(new Response(200, [], $queryResponse));

        // Successfull deleting an entity from manage results in return type boolean : true
        $this->prodMockHandler->append(new Response(200, [], json_encode(true)));

        $crawler = $this->client->request('GET', "/entity/delete/published/1/a8e7cffd-0409-45c7-a37a-000000000000/production");

        $pageTitle = $crawler->filter('.page-container h1');

        $this->assertEquals('Delete entity', $pageTitle->text());

        $form = $crawler->filter('.page-container')
            ->selectButton('Delete')
            ->form();

        $this->client->submit($form);

        $this->assertTrue(
            $this->client->getResponse() instanceof RedirectResponse,
            'Expecting a redirect response after editing an entity'
        );
    }

    /**
     * The acceptance test environment does not test against a Jira mock server and
     * thus, only a 'jira is down' web test is provided.
     */
    public function test_request_delete_a_published_production_entity_jira_not_available()
    {
        $queryResponse = json_encode([
            'id' => 'a8e7cffd-0409-45c7-a37a-000000000000',
            'data' => (object)[
                'entityid' => 'SP1',
                'metaDataFields' => [
                    'name:en' => 'SP1',
                    'contacts:0:contactType' => 'administrative',
                    'contacts:0:givenName' => 'Test',
                    'contacts:0:surName' => 'Test',
                    'contacts:0:emailAddress' => 'test@example.org',
                ],
            ],
        ]);

        // Rendering the form requires retrieval of the manage entity
        $this->prodMockHandler->append(new Response(200, [], $queryResponse));
        // Handling the form also requires retrieval of the manage entity
        $this->prodMockHandler->append(new Response(200, [], $queryResponse));
        $this->prodMockHandler->append(new Response(200, [], $queryResponse));

        // The entity list action
        $this->testMockHandler->append(new Response(200, [], '[]'));
        $this->testMockHandler->append(new Response(200, [], '[]'));
        $this->prodMockHandler->append(new Response(200, [], '[]'));

        $crawler = $this->client->request('GET', "/entity/delete/request/1/a8e7cffd-0409-45c7-a37a-000000000000");

        $pageTitle = $crawler->filter('h1');

        $this->assertEquals('Delete entity', $pageTitle->text());

        $form = $crawler->filter('.page-container')
            ->selectButton('Delete')
            ->form();

        $this->client->submit($form);

        $this->assertTrue(
            $this->client->getResponse() instanceof RedirectResponse,
            'Expecting a redirect response after opening a entity delete request ticket'
        );

        $crawler = $this->client->followRedirect();


        $flashMessage = $crawler->filter('div.message.error');

        $this->assertEquals(
            'Oops, creating the delete request failed. Our ticket service might have been offline. Please try again at a later time.',
            $flashMessage->text()
        );
    }
}
