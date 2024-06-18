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

class EntityDetailTest extends WebTestCase
{

    public function test_render_details_of_removal_requested_manage_entity()
    {
        $this->loadFixtures();
        $this->logIn();
        $entityId = '9729d851-cfdd-4283-a8f1-a29ba5036261';
        $this->registerManageEntity(
            'production',
            'saml20_sp',
            $entityId,
            'SP3',
            'https://sp1-entityid.example.com',
            'https://sp1-entityid.example.com/metadata',
            'urn:collab:group:vm.openconext.org:demo:openconext:org:surf.nl'
        );

        $issueType = 'spd-delete-production-entity';
        $this->jiraIssueRepository->shouldNotFailCreateIssue();
        $this->createjiraTicket($entityId, $issueType);

        $this->switchToService('SURFnet');

        $crawler = self::$pantherClient->request('GET', sprintf('/entity/detail/1/%s/production', $entityId));

        self::assertOnPage("Entity details");
        // Only the back to overview link should be on the actions toolbar
        $toolbar = $crawler->filter('.fieldset.card.action');
        $actions = $toolbar->filter('a[data-testid]');
        $this->assertCount(1, $actions);
        $this->assertEquals('Back to overview', $actions->first()->getText());
    }


    public function test_render_details_of_manage_entity()
    {
        $this->loadFixtures();
        $this->logIn();

        $this->registerManageEntity(
            'production',
            'saml20_sp',
            '9729d851-cfdd-4283-a8f1-a29ba5036261',
            'SP3',
            'https://sp1-entityid.example.com',
            'https://sp1-entityid.example.com/metadata',
            'urn:collab:group:vm.openconext.org:demo:openconext:org:surf.nl'
        );

        $this->switchToService('SURFnet');

        $crawler = self::$pantherClient->request('GET', '/entity/detail/1/9729d851-cfdd-4283-a8f1-a29ba5036261/production');

        self::assertOnPage("Entity details");

        $this->assertDetailEquals(0, 'Metadata URL', 'https://sp1-entityid.example.com/metadata');
        $this->assertDetailsAscLocationEquals(1, 'ACS location', 'https://sp1-entityid.example.com/acs');
        $this->assertDetailEquals(2, 'Entity ID', 'https://sp1-entityid.example.com');
        $this->assertDetailEquals(8, 'Name EN', 'SP3 Name English');
        $this->assertDetailEquals(10, 'First name', 'givenname', true);
        $this->assertDetailEquals(11, 'Last name', 'surname', false);
    }


    /**
     * @see https://www.pivotaltracker.com/story/show/165597382
     */
    public function test_it_shows_attributes_without_motivation()
    {
        $this->registerManageEntity(
            'production',
            'saml20_sp',
            '9729d851-cfdd-4283-a8f1-a29ba5036261',
            'SP3',
            'https://sp1-entityid.example.com',
            'https://sp1-entityid.example.com/metadata',
            'urn:collab:group:vm.openconext.org:demo:openconext:org:surf.nl'
        );

        $this->loadFixtures();
        $this->logIn();

        $this->switchToService('SURFnet');

        self::$pantherClient->request('GET', '/entity/detail/1/9729d851-cfdd-4283-a8f1-a29ba5036261/production');

        $this->assertAttributeDetailEquals(0, 'Display name attribute', 'Test Attribute 1');
        $this->assertAttributeDetailEquals(1, 'Email address attribute', 'No motivation set');
    }

    public function test_if_attribute_has_expected_number_of_elements()
    {
        $this->registerManageEntity(
            'production',
            'saml20_sp',
            '9729d851-cfdd-4283-a8f1-a29ba5036261',
            'SP3',
            'https://sp1-entityid.example.com',
            'https://sp1-entityid.example.com/metadata',
            'urn:collab:group:vm.openconext.org:demo:openconext:org:surf.nl'
        );

        $this->loadFixtures();
        $this->logIn();

        $this->switchToService('SURFnet');

        self::$pantherClient->request('GET', '/entity/detail/1/9729d851-cfdd-4283-a8f1-a29ba5036261/production');
        $this->assertNumberOfAttributeElementsEquals(0, 2);
        $this->assertNumberOfAttributeElementsEquals(1, 2);
    }


    private function assertListContains($position, $expectedLabel, array $expectedValues)
    {
        $rows = self::$pantherClient->getCrawler()->filter('div.detail');
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
        $rows = self::$pantherClient->getCrawler()->filter('div.detail');
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
        $rows = self::$pantherClient->getCrawler()->filter('div.detail');
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

    private function assertDetailsAscLocationEquals($position, $expectedLabel, $expectedValue, $hasTooltip = true)
    {
        $rows = self::$pantherClient->getCrawler()->filter('div.detail');
        $row = $rows->eq($position);
        $label = $row->filter('label')->text();
        $ul = $row->filter('ul');
        $li = $row->filter('li');
        $this->assertCount(1, $ul);
        $this->assertCount(1, $li);

        $this->assertEquals(
            $expectedLabel,
            $label,
            sprintf('Expected label "%s" at the row on position %d', $expectedLabel, $position)
        );
    }

    private function assertAttributeDetailEquals($position, $expectedTitle, $expectedValue)
    {
        $rows = self::$pantherClient->getCrawler()->filter('div.detail.attribute');
        $headings = self::$pantherClient->getCrawler()->filter('h3.attribute-title');
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

    private function assertNumberOfAttributeElementsEquals($position, $expectedNumberOfElements)
    {
        $rows = self::$pantherClient->getCrawler()->filter('div.detail.attribute');
        $row = $rows->eq($position);
        $numberOfRowElements = $row->children()->count();
        $this->assertEquals(
            $row->children()->count(),
            $expectedNumberOfElements,
            sprintf('The actual number of elements (%d) in the div.detail.attribute row does not match the expected %d', $numberOfRowElements, $expectedNumberOfElements)
        );
    }
}
