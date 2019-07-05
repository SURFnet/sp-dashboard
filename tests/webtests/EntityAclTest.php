<?php

/**
 * Copyright 2019 SURFnet B.V.
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

class EntityAclTest extends WebTestCase
{
    private $entityId;
    private $serviceId;

    public function setUp()
    {
        parent::setUp();

        $this->loadFixtures();

        $this->logIn('ROLE_ADMINISTRATOR');

        $service = $this->getServiceRepository()->findByName('SURFnet');

        $this->getAuthorizationService()->changeActiveService($service->getId());

        $entity = $service->getEntities()->first();

        $this->entityId = $entity->getId();
        $this->serviceId = $entity->getService()->getId();
    }

    public function test_it_renders_the_form()
    {
        $spQueryResponse = json_encode([
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
        $idpQueryResponse = json_encode([
            [
                '_id' => 'bfe8f00d-317a-4fbc-9cf8-ad2f3b2af578',
                'version' => 1,
                'data' =>
                    [
                        'entityid' => 'http://mock-idp',
                        'state' => 'prodaccepted',
                        'notes' => null,
                        'metaDataFields' =>
                            [
                                'name:en' => 'OpenConext Mujina IDP',
                                'name:nl' => 'OpenConext Mujina IDP',
                            ],
                    ],
            ],
            [
                '_id' => '0c3febd2-3f67-4b8a-b90d-ce56a3b0abb4',
                'version' => 0,
                'data' =>
                    [
                        'entityid' => 'https://engine.dev.support.surfconext.nl/authentication/idp/metadata',
                        'state' => 'prodaccepted',
                        'notes' => null,
                        'metaDataFields' =>
                            [
                                'name:en' => 'OpenConext Engine',
                                'name:nl' => 'OpenConext Engine',
                            ],
                    ],
            ],
        ]);
        $this->testMockHandler->append(new Response(200, [], $spQueryResponse));
        $this->testMockHandler->append(new Response(200, [], $idpQueryResponse));

        $crawler = $this->client->request('GET', "/entity/acl/{$this->serviceId}/{$this->entityId}");

        $form = $crawler->filter('.page-container')
            ->selectButton('Save')
            ->form();
        $selectAllInput = $form->get('acl_entity[selectAll]');
        $this->assertEquals(
            1,
            $selectAllInput->getValue(),
            'Expect the selectAll field to be set'
        );
    }
}
