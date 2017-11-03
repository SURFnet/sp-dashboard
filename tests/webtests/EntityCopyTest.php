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

class EntityCopyTest extends WebTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->loadFixtures();
        $this->logIn('ROLE_ADMINISTRATOR');

        $this->service = $this->getServiceRepository()->findByName('SURFnet');

        $this->getAuthorizationService()->setSelectedServiceId($this->service->getId());
    }

    public function test_copy_creates_new_entity()
    {
        $response = file_get_contents(
            __DIR__ . '/fixtures/entity-copy/remote-entity-info.json'
        );
        $this->mockHandler->append(new Response(200, [], $response));

        $response = file_get_contents(
            __DIR__ . '/fixtures/entity-copy/remote-metadata.json'
        );
        $this->mockHandler->append(new Response(200, [], json_decode($response)));

        $this->client->request('GET', "/entity/copy/d645ddf7-1246-4224-8e14-0d5c494fd9ad");

        $this->assertTrue(
            $this->client->getResponse() instanceof RedirectResponse,
            'Expecting a redirect response after editing an entity'
        );

        $this->mockHandler->append(new Response(200, [], '[]'));

        $crawler = $this->client->followRedirect();

        $pageTitle = $crawler->filter('.page-container h1');

        $this->assertEquals('Service Provider registration form', $pageTitle->text());

        $form = $crawler->selectButton('Publish')->form();

        $this->assertEquals(
            'https://engine.dev.support.surfconext.nl/authentication/sp/consume-assertion',
            $form->get('dashboard_bundle_edit_entity_type[metadata][acsLocation]')->getValue()
        );

        $this->assertEquals(
            'https://engine.dev.support.surfconext.nl/authentication/sp/metadata/1430',
            $form->get('dashboard_bundle_edit_entity_type[metadata][entityId]')->getValue()
        );

        $this->assertEquals(
            'OpenConext Engine EN',
            $form->get('dashboard_bundle_edit_entity_type[metadata][nameEn]')->getValue()
        );

        $this->assertEquals(
            'OpenConext SSO Proxy EN',
            $form->get('dashboard_bundle_edit_entity_type[metadata][descriptionEn]')->getValue()
        );

        $this->assertEquals(
            'OpenConext Engine',
            $form->get('dashboard_bundle_edit_entity_type[metadata][nameNl]')->getValue()
        );

        $this->assertEquals(
            'OpenConext SSO Proxy',
            $form->get('dashboard_bundle_edit_entity_type[metadata][descriptionNl]')->getValue()
        );

        $this->assertEquals(
            'Support1430',
            $form->get('dashboard_bundle_edit_entity_type[contactInformation][administrativeContact][firstName]')->getValue()
        );

        $this->assertEquals(
            'OpenConext1430',
            $form->get('dashboard_bundle_edit_entity_type[contactInformation][administrativeContact][lastName]')->getValue()
        );

        $this->assertEquals(
            'UID motivation',
            $form->get('dashboard_bundle_edit_entity_type[attributes][uidAttribute][motivation]')->getValue()
        );
    }

    public function test_copy_redirects_to_existing_entity()
    {
        $entity = $this->service->getEntities()->first();
        $entity->setManageid('d645ddf7-1246-4224-8e14-0d5c494fd9ad');
        $this->getEntityRepository()->save($entity);

        $this->client->request('GET', '/entity/copy/d645ddf7-1246-4224-8e14-0d5c494fd9ad');

        $this->assertTrue(
            $this->client->getResponse() instanceof RedirectResponse,
            'Expecting a redirect response after copying an entity'
        );

        $this->assertEquals(
            $this->client->getResponse()->headers->get('location'),
            '/entity/edit/' . $entity->getId(),
            'Expecting a redirect response to existing entity after copying'
        );
    }
}
