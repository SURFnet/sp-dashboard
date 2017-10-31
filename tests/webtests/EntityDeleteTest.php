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

class EntityDeleteTest extends WebTestCase
{
    private $entityId;

    public function setUp()
    {
        parent::setUp();

        $this->loadFixtures();

        $this->logIn('ROLE_ADMINISTRATOR');

        $service = $this->getServiceRepository()->findByName('SURFnet');

        $this->getAuthorizationService()->setSelectedServiceId($service->getId());

        $this->entity = $service->getEntities()
            ->first();
    }

    public function test_delete_returns_to_entity_list()
    {
        $crawler = $this->client->request('GET', "/entity/delete/{$this->entity->getId()}");

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

        $this->assertNotContains($this->entity->getNameEn(), $row->text());
    }

    public function test_cancel_returns_to_entity_list()
    {
        $crawler = $this->client->request('GET', "/entity/delete/{$this->entity->getId()}");

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

        $crawler = $this->client->followRedirect();

        $row = $crawler->filter('table tr')->eq(1);

        $this->assertContains($this->entity->getNameEn(), $row->text(), 'The entity should not have been deleted after cancel');
    }
}
