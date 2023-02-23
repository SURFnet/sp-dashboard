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

use Surfnet\ServiceProviderDashboard\Webtests\Debug\DebugFile;

class EntityDeleteTest extends WebTestCase
{

    public function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures();
        $this->logIn();
        $this->service = $this->getServiceRepository()->findByName('SURFnet');
        $this->ticketService = self::getContainer()->get('surfnet.dashboard.repository.issue');
        $this->switchToService('SURFnet');
    }

    public function test_delete_a_published_test_entity()
    {
        $this->registerManageEntity(
            'test',
            'saml20_sp',
            'a8e7cffd-0409-45c7-a37a-000000000000',
            'SP1',
            'SP1',
            'https://sp1-entityid.example.com/metadata',
            'urn:collab:group:vm.openconext.org:demo:openconext:org:surf.nl'
        );

        $this->testDeleteClient->registerDeleteRequest('a8e7cffd-0409-45c7-a37a-000000000000');

        $crawler = self::$pantherClient->request('GET', "/entity/delete/published/1/a8e7cffd-0409-45c7-a37a-000000000000");

        $pageTitle = $crawler->filter('.page-container h1');

        $this->assertEquals('Delete entity', $pageTitle->text());

        $form = $crawler->filter('.page-container')
            ->selectButton('Delete')
            ->form();

        self::$pantherClient->submit($form);
        self::assertOnPage('Your entity is deleted');
    }

    public function test_delete_a_unpublished_production_entity()
    {
        $this->registerManageEntity(
            'production',
            'saml20_sp',
            'a8e7cffd-0409-45c7-a37a-000000000000',
            'SP1',
            'SP1',
            'https://sp1-entityid.example.com/metadata',
            'urn:collab:group:vm.openconext.org:demo:openconext:org:surf.nl'
        );

        $this->prodDeleteClient->registerDeleteRequest('a8e7cffd-0409-45c7-a37a-000000000000');

        $crawler = self::$pantherClient->request('GET', "/entity/delete/published/1/a8e7cffd-0409-45c7-a37a-000000000000/production");

        $pageTitle = $crawler->filter('.page-container h1');

        $this->assertEquals('Delete entity', $pageTitle->text());

        $form = $crawler->filter('.page-container')
            ->selectButton('Delete')
            ->form();

        self::$pantherClient->submit($form);
        self::assertOnPage('Your entity is deleted');

    }

    /**
     * The acceptance test environment does not test against a Jira mock server and
     * thus, only a 'jira is down' web test is provided.
     */
    public function test_request_delete_a_published_production_entity_jira_not_available()
    {
        $this->registerManageEntity(
            'production',
            'saml20_sp',
            'a8e7cffd-0409-45c7-a37a-000000000000',
            'SP1',
            'SP1',
            'https://sp1-entityid.example.com/metadata',
            'urn:collab:group:vm.openconext.org:demo:openconext:org:surf.nl'
        );

        $this->ticketService->shouldFailCreateIssue();

        $crawler = self::$pantherClient->request('GET', "/entity/delete/request/1/a8e7cffd-0409-45c7-a37a-000000000000");
        $pageTitle = $crawler->filter('h1');
        $this->assertEquals('Delete entity', $pageTitle->text());

        $form = $crawler->filter('.page-container')
            ->selectButton('Delete')
            ->form();

        self::$pantherClient->submit($form);
        DebugFile::takeScreenshot(self::$pantherClient);
        $this->assertOnPage(
            'Oops, creating the delete request failed. Our ticket service might have been offline. Please try again at a later time.'
        );
    }
}
