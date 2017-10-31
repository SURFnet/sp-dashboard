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
    public function test_delete_returns_to_entity_list()
    {
        $this->loadFixtures();

        $this->logIn('ROLE_ADMINISTRATOR');

        $service = $this->getServiceRepository()->findByName('SURFnet');
        $this->getAuthorizationService()->setSelectedServiceId(
            $service->getId()
        );

        $entity = $service->getEntities()->first();

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

        $location = $this->client->getResponse()->headers->get('location');

        // Note: after a redirect response we do not have access to the container anymore.
        // To work around this problem, we do the setup again and visit the redirection target.
        //
        // See: https://github.com/symfony/symfony/issues/8858
        $this->setUp();

        $this->logIn('ROLE_ADMINISTRATOR');

        $this->getAuthorizationService()->setSelectedServiceId($service->getId());

        $this->mockHandler->append(new Response(200, [], '[]'));

        $crawler = $this->client->request('GET', $location);

        $row = $crawler->filter('table tr')->eq(1);

        $this->assertNotContains($entity->getNameEn(), $row->text());
    }

    public function test_cancel_returns_to_entity_list()
    {
        $this->loadFixtures();

        $this->logIn('ROLE_ADMINISTRATOR');

        $service = $this->getServiceRepository()->findByName('SURFnet');

        $this->getAuthorizationService()->setSelectedServiceId($service->getId());

        $entity = $service->getEntities()->first();

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

        $location = $this->client->getResponse()->headers->get('location');

        // Note: after a redirect response we do not have access to the container anymore.
        // To work around this problem, we do the setup again and visit the redirection target.
        //
        // See: https://github.com/symfony/symfony/issues/8858
        $this->setUp();

        $this->logIn('ROLE_ADMINISTRATOR');

        $service = $this->getServiceRepository()->findByName('SURFnet');

        $this->getAuthorizationService()->setSelectedServiceId($service->getId());

        $this->mockHandler->append(new Response(200, [], '[]'));

        $crawler = $this->client->request('GET', $location);

        $row = $crawler->filter('table tr')->eq(1);

        $this->assertContains($entity->getNameEn(), $row->text(), 'The entity should not have been deleted after cancel');
    }
}
