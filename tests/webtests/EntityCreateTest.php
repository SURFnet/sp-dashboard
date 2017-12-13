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

        $this->logIn('ROLE_ADMINISTRATOR');

        $this->getAuthorizationService()->setSelectedServiceId(
            $this->getServiceRepository()->findByName('Ibuildings B.V.')->getId()
        );
    }

    public function test_it_renders_the_form()
    {
        $crawler = $this->client->request('GET', "/entity/create");
        $form = $crawler->filter('.page-container')
            ->selectButton('Save')
            ->form();
        $nameEnfield = $form->get('dashboard_bundle_entity_type[metadata][nameEn]');
        $this->assertEquals(
            '',
            $nameEnfield->getValue(),
            'Expect the NameEN field to be empty'
        );
    }
}
