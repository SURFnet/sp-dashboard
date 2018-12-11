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
use Symfony\Component\DomCrawler\Crawler;

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

        $this->assertDetailEquals(0, 'Entity ID', 'SP1');
        $this->assertDetailEquals(1, 'Name EN', 'SP1');
        $this->assertDetailEquals(2, 'First name', 'John', false);
        $this->assertDetailEquals(3, 'Last name', 'Doe', false);
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

        $this->prodMockHandler->append(new Response(200, [], $sp3QueryResponse));

        $this->getAuthorizationService()->setSelectedServiceId(
            $this->getServiceRepository()->findByName('SURFnet')->getId()
        );

        $crawler = $this->client->request('GET', '/entity/detail/9729d851-cfdd-4283-a8f1-a29ba5036261/production');

        $pageTitle = $crawler->filter('.page-container h1');

        $this->assertContains("Entity details", $pageTitle->text());

        $this->assertDetailEquals(0, 'Entity ID', 'SP3');
        $this->assertDetailEquals(1, 'Name EN', 'SP3');
        $this->assertDetailEquals(2, 'First name', 'Test', false);
        $this->assertDetailEquals(3, 'Last name', 'Test', false);
    }

    private function assertDetailEquals($position, $expectedLabel, $expectedValue, $hasTooltip = true)
    {
        $rows = $this->client->getCrawler()->filter('div.detail');
        $row = $rows->eq($position);
        $label = $row->filter('label')->text();
        $spans = $row->filter('span');
        if ($hasTooltip) {
            $this->assertCount(2, $spans);
            $valueSpan = $spans->eq(1)->text();
        } else {
            $this->assertCount(1, $spans);
            // If the tooltip is not present, there is only one span in the div.
            $valueSpan = $spans->text();
        }

        $this->assertEquals(
            $expectedLabel,
            $label,
            sprintf('Expected label "%s" at the row on position %d', $expectedLabel, $position)
        );
        $this->assertEquals(
            $expectedValue,
            $valueSpan,
            sprintf('Expected span "%s" at the row on position %d', $expectedValue, $position)
        );
    }
}
