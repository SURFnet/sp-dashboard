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

use GuzzleHttp\Psr7\Response;

class EntityDetailTest extends WebTestCase
{
    public function test_render_details_of_draft_entity()
    {
        $this->loadFixtures();
        $this->logIn('ROLE_ADMINISTRATOR');

        $this->getAuthorizationService()->setSelectedServiceId(
            $this->getServiceRepository()->findByName('SURFnet')->getId()
        );

        $entity = reset($this->getEntityRepository()->findBy(['nameEn' => 'SP1']));

        $crawler = $this->client->request('GET', sprintf('/entity/detail/%s', $entity->getId()));

        $pageTitle = $crawler->filter('.page-container h1');

        $this->assertContains("Entity details", $pageTitle->text());

        $rows = $crawler->filter('div.detail');
        $this->assertEquals('Entity ID', $rows->eq(0)->filter('label')->text());
        $this->assertEquals('SP1', $rows->eq(0)->filter('span')->text());
        $this->assertEquals('Name EN', $rows->eq(1)->filter('label')->text());
        $this->assertEquals('SP1', $rows->eq(1)->filter('span')->text());
        $this->assertEquals('First name', $rows->eq(2)->filter('label')->text());
        $this->assertEquals('John', $rows->eq(2)->filter('span')->text());
        $this->assertEquals('Last name', $rows->eq(3)->filter('label')->text());
        $this->assertEquals('Doe', $rows->eq(3)->filter('span')->text());
    }

    public function test_render_details_of_manage_entity()
    {
        $this->loadFixtures();
        $this->logIn('ROLE_ADMINISTRATOR');

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

        $this->testMockHandler->append(new Response(200, [], $sp3QueryResponse));

        $this->getAuthorizationService()->setSelectedServiceId(
            $this->getServiceRepository()->findByName('SURFnet')->getId()
        );

        $crawler = $this->client->request('GET', '/entity/detail/production/9729d851-cfdd-4283-a8f1-a29ba5036261');

        $pageTitle = $crawler->filter('.page-container h1');

        $this->assertContains("Entity details", $pageTitle->text());

        $rows = $crawler->filter('div.detail');
        $this->assertEquals('Entity ID', $rows->eq(0)->filter('label')->text());
        $this->assertEquals('SP3', $rows->eq(0)->filter('span')->text());
        $this->assertEquals('Name EN', $rows->eq(1)->filter('label')->text());
        $this->assertEquals('SP3', $rows->eq(1)->filter('span')->text());
        $this->assertEquals('First name', $rows->eq(2)->filter('label')->text());
        $this->assertEquals('Test', $rows->eq(2)->filter('span')->text());
        $this->assertEquals('Last name', $rows->eq(3)->filter('label')->text());
        $this->assertEquals('Test', $rows->eq(3)->filter('span')->text());
    }
}
