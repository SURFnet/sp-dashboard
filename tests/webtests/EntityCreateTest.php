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

use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\RedirectResponse;

class EntityCreateTest extends WebTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->loadFixtures();

        $this->getAuthorizationService()->setSelectedServiceId(
            $this->getServiceRepository()->findByName('Ibuildings B.V.')->getId()
        );
    }

    public function test_entity_can_be_created()
    {
        $this->logIn('ROLE_ADMINISTRATOR');

        $this->client->request('GET', '/entity/create');

        $this->assertTrue(
            $this->client->getResponse() instanceof RedirectResponse,
            'Expecting a redirect response after creating an entity'
        );

        $this->client->followRedirect();

        $service = $this->getServiceRepository()->findByName('Ibuildings B.V.');
        $entities = $service->getEntities();

        // One Service has been created
        $this->assertCount(1, $entities);

        /** @var Entity $entity */
        $entity = $entities->last();

        // The Id and TicketNumber fields are Uuids
        $this->assertNotEmpty($entity->getId());

        $this->assertEquals(Entity::ENVIRONMENT_TEST, $entity->getEnvironment());
        $this->assertEquals('Ibuildings B.V.', $entity->getService()->getName());
    }
}
