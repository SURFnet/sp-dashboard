<?php

/**
 * Copyright 2018 SURFnet B.V.
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

use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;

class EntityCreateOidcTest extends WebTestCase
{
    /**
     * @var Service
     */
    private $service;

    public function setUp()
    {
        parent::setUp();
        $this->loadFixtures();
        $this->logIn('ROLE_ADMINISTRATOR');
        $this->service = $this->getServiceRepository()->findByName('Ibuildings B.V.');
        $this->switchToService('Ibuildings B.V.');
        $this->service = $this->getServiceRepository()->findByName('SURFnet');
    }

    public function test_it_renders_the_form()
    {
        $crawler = $this->client->request('GET', "/entity/create/2/oidc/test");
        $pageTitle = $crawler->filter('h1.break-long-words')->first()->text();
        $this->assertEquals("OIDC enitty have been made read-only. Use OIDC TNG entities instead.", $pageTitle);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }
}
