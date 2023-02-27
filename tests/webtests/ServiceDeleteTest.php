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

use Surfnet\ServiceProviderDashboard\Webtests\Debug\DebugFile;

class ServiceDeleteTest extends WebTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures();
        $this->registerManageEntity(
            'test',
            'saml20_sp',
            '9729d851-cfdd-4283-a8f1-a29ba5036261',
            'SP1',
            'https://sp1-entityid.example.com',
            'https://sp1-entityid.example.com/metadata',
            'urn:collab:group:vm.openconext.org:demo:openconext:org:surf.nl'
        );
        $this->registerManageEntity(
            'test',
            'saml20_sp',
            '7398d851-abd1-2283-a8f1-a29ba5036174',
            'SP2',
            'https://sp2-entityid.example.com',
            'https://sp2-entityid.example.com/metadata',
            'urn:collab:group:vm.openconext.org:demo:openconext:org:surf.nl'
        );
        $this->testDeleteClient->registerDeleteRequest('9729d851-cfdd-4283-a8f1-a29ba5036261');
        $this->testDeleteClient->registerDeleteRequest('7398d851-abd1-2283-a8f1-a29ba5036174');

        $this->teamsQueryClient->registerTeam('demo:openconext:org:surf.nl', '{"teamId": 1}');
        $this->logIn();
    }

    public function test_removing_a_service_redirects_to_service_overview()
    {
        $this->switchToService('SURFnet');

        self::$pantherClient->request('GET', '/service/1/edit');
        self::findBy('#dashboard_bundle_edit_service_type_delete')->click();

        $this->assertOnPage("You are about to delete 'SURFnet'. Are you sure?");
        $this->assertOnPage("With this, you are also deleting the following entities");

        $crawler = self::$pantherClient->refreshCrawler();
        // Assert the entities of the service are listed on the page
        $entities = $crawler->filter('table.entities tbody tr');
        // The two SURFnet entities are in the list.
        $this->assertCount(2, $entities, 'The two pre configured entities should be listed on the confirmation page');

        self::findBy('#dashboard_bundle_delete_service_type_delete')->click();
        DebugFile::scrollDown(self::$pantherClient, 20);
        DebugFile::takeScreenshot(self::$pantherClient);

        self::assertOnPage('Your service was deleted.');
    }

    /**
     * Removing a service with privacy questions should not result in integrity constraint violation errors
     *
     * See Pivotal Tracker; https://www.pivotaltracker.com/story/show/165237921
     */
    public function test_removing_a_service_with_privacy_questions_is_possible()
    {
        $this->switchToService('Ibuildings B.V.');
        self::$pantherClient->request('GET', '/service/2/edit');

        self::findBy('#dashboard_bundle_edit_service_type_delete')->click();
        $this->assertOnPage("You are about to delete 'Ibuildings'. Are you sure?");

        self::findBy('#dashboard_bundle_delete_service_type_delete')->click();
        self::assertOnPage('Your service was deleted.');
    }
}
