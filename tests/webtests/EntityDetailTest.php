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
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\OidcGrantType;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Dto\Protocol;

class EntityDetailTest extends WebTestCase
{
    public function test_render_details_of_draft_entity()
    {
        $this->loadFixtures();
        $this->logIn('ROLE_ADMINISTRATOR');

        $this->switchToService('SURFnet');

        $entity = reset($this->getEntityRepository()->findBy(['nameEn' => 'SP1']));

        $crawler = $this->client->request('GET', sprintf('/entity/detail/1/%s', $entity->getId()));

        $pageTitle = $crawler->filter('.page-container h1');

        $this->assertContains("Entity details", $pageTitle->text());

        $this->assertDetailEquals(0, 'Entity ID', 'SP1');
        $this->assertDetailEquals(1, 'Name EN', 'SP1');
        $this->assertDetailEquals(2, 'First name', 'John', true);
        $this->assertDetailEquals(3, 'Last name', 'Doe', false);
    }

    /**
     * See: https://www.pivotaltracker.com/story/show/164598856
     */
    public function test_render_details_of_oidc_draft_entity()
    {
        $this->loadFixtures();
        $this->logIn('ROLE_ADMINISTRATOR');

        $this->switchToService('SURFnet');

        /** @var Entity $entity */
        $entity = reset($this->getEntityRepository()->findBy(['nameEn' => 'SP1']));
        $entity->setProtocol(Entity::TYPE_OPENID_CONNECT);
        $entity->setGrantType(new OidcGrantType(OidcGrantType::GRANT_TYPE_AUTHORIZATION_CODE));
        $entity->setRedirectUris(['https://sp.example.org', 'https://sp.example.org/redirect2']);
        $entity->setEnablePlayground(true);
        $this->getEntityRepository()->save($entity);

        $this->client->request('GET', sprintf('/entity/detail/1/%s', $entity->getId()));

        $this->assertListContains(1, 'Redirect URIs', ['https://sp.example.org', 'https://sp.example.org/redirect2']);
        $this->assertDetailEquals(2, 'Grant type', 'Authorization code');
        $this->assertIsChecked(3, 'Playground enabled?');
    }

    public function test_render_details_of_manage_entity()
    {
        $this->loadFixtures();
        $this->logIn('ROLE_ADMINISTRATOR');

        $sp3QueryResponse = json_encode((object)[
            'id' => '9729d851-cfdd-4283-a8f1-a29ba5036261',
            'type' => Protocol::SAML20_SP,
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

        $this->switchToService('SURFnet');

        $crawler = $this->client->request('GET', '/entity/detail/1/9729d851-cfdd-4283-a8f1-a29ba5036261/production');

        $pageTitle = $crawler->filter('.page-container h1');

        $this->assertContains("Entity details", $pageTitle->text());

        $this->assertDetailEquals(0, 'Entity ID', 'SP3');
        $this->assertDetailEquals(1, 'Name EN', 'SP3');
        $this->assertDetailEquals(2, 'First name', 'Test', true);
        $this->assertDetailEquals(3, 'Last name', 'Test', false);
    }


    /**
     * @see https://www.pivotaltracker.com/story/show/165597382
     */
    public function test_it_shows_attributes_without_motivation()
    {
        $this->loadFixtures();
        $this->logIn('ROLE_ADMINISTRATOR');

        $this->switchToService('SURFnet');

        /** @var \Surfnet\ServiceProviderDashboard\Domain\Entity\Entity $entity */
        $entity = reset($this->getEntityRepository()->findBy(['nameEn' => 'SP1']));

        $commonNameAttribute = new Attribute();
        $commonNameAttribute->setRequested(true);
        $commonNameAttribute->setMotivation('Hi there, we want to know your name!');

        // No motivation is set on this attribute
        $organizationAttribute = new Attribute();
        $organizationAttribute->setRequested(true);

        $entity->setCommonNameAttribute($commonNameAttribute);
        $entity->setOrganizationAttribute($organizationAttribute);

        $this->getEntityRepository()->save($entity);

        $this->client->request('GET', sprintf('/entity/detail/1/%s', $entity->getId()));

        $this->assertAttributeDetailEquals(0, 'Common name attribute', 'Hi there, we want to know your name!');
        $this->assertAttributeDetailEquals(1, 'Organization attribute', 'No motivation set');
    }

    private function assertListContains($position, $expectedLabel, array $expectedValues)
    {
        $rows = $this->client->getCrawler()->filter('div.detail');
        $row = $rows->eq($position);
        $label = $row->filter('label')->text();
        $listItems = $row->filter('li');
        $this->assertCount(count($expectedValues), $listItems);

        $this->assertEquals(
            $expectedLabel,
            $label,
            sprintf('Expected label "%s" at the row on position %d', $expectedLabel, $position)
        );
        foreach ($listItems as $node) {
            $listItemValue = $node->nodeValue;
            $this->assertContains(
                $listItemValue,
                $expectedValues,
                sprintf('Expected list item "%s" to be in "%s"', $listItemValue, implode(', ', $expectedValues))
            );
        }
    }

    private function assertIsChecked($position, $expectedLabel)
    {
        $rows = $this->client->getCrawler()->filter('div.detail');
        $row = $rows->eq($position);
        $label = $row->filter('label')->text();
        $icon = $row->filter('i')->last();
        $iconClasses = $icon->attr('class');

        $this->assertEquals(
            $expectedLabel,
            $label,
            sprintf('Expected label "%s" at the row on position %d', $expectedLabel, $position)
        );
        $this->assertEquals(
            'fa fa-check-square',
            $iconClasses,
            'Expected to find the check-square class on the icon class.'
        );
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

    private function assertAttributeDetailEquals($position, $expectedTitle, $expectedValue)
    {
        $rows = $this->client->getCrawler()->filter('div.detail.attribute');
        $headings = $this->client->getCrawler()->filter('h3.attribute-title');
        $row = $rows->eq($position);
        $heading = $headings->eq($position);

        $attributeValue = $row->filter('span')->last()->text();
        $attributeTitle = $heading->text();

        $this->assertEquals(
            $expectedTitle,
            $attributeTitle,
            sprintf('Expected attribute name "%s" at the row on position %d', $attributeTitle, $position)
        );
        $this->assertEquals(
            $expectedValue,
            $attributeValue,
            sprintf('Expected attribute value "%s" at the row on position %d', $expectedValue, $position)
        );
    }
}
