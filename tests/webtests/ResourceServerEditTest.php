<?php

/**
 * Copyright 2026 SURFnet B.V.
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

use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\DataFixtures\ORM\WebTestFixtures;

class ResourceServerEditTest extends WebTestCase
{
    private string $rsManageId = 'aabb1234-cfdd-4283-a8f1-a29ba5036262';
    private string $rsEntityId = 'https://rs.example.com';

    public function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures();

        $this->registerManageEntityRaw('test', json_encode([
            'id' => $this->rsManageId,
            'type' => 'oauth20_rs',
            'data' => [
                'entityid' => $this->rsEntityId,
                'state' => 'testaccepted',
                'active' => true,
                'metaDataFields' => [
                    'NameIDFormat' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',
                    'name:en' => 'Test Resource Server',
                    'name:nl' => 'Test Resource Server NL',
                    'description:en' => 'A test resource server',
                    'description:nl' => 'Een test resource server',
                    'contacts:0:contactType' => 'support',
                    'contacts:0:givenName' => 'John',
                    'contacts:0:surName' => 'Doe',
                    'contacts:0:emailAddress' => 'john.support@example.com',
                    'contacts:1:contactType' => 'administrative',
                    'contacts:1:givenName' => 'Jane',
                    'contacts:1:surName' => 'Doe',
                    'contacts:1:emailAddress' => 'jane.admin@example.com',
                    'contacts:2:contactType' => 'technical',
                    'contacts:2:givenName' => 'Jim',
                    'contacts:2:surName' => 'Doe',
                    'contacts:2:emailAddress' => 'jim.tech@example.com',
                    'coin:service_team_id' => WebTestFixtures::TEAMNAME_SURF,
                    'secret' => '$2a$05$dJoujExEfn9HPtF9p5uYFuCl7AfzyRdNiEeM5oFoAq9KUF6RrZY9C',
                ],
                'type' => 'oauth20-rs',
            ],
        ]));

        $this->testPublicationClient->registerPublishResponse(
            $this->rsEntityId,
            json_encode(['id' => $this->rsManageId])
        );

        $this->logIn();
        $this->switchToService('SURFnet');
    }

    public function testEditingExistingResourceServerSucceeds(): void
    {
        $crawler = self::$pantherClient->request('GET', "/entity/edit/test/{$this->rsManageId}/1");

        $form = $crawler->selectButton('Publish')->form();

        $form->setValues([
            'dashboard_bundle_entity_type[metadata][nameEn]' => 'Updated Resource Server',
            'dashboard_bundle_entity_type[metadata][nameNl]' => 'Bijgewerkte Resource Server NL',
        ]);

        self::$pantherClient->submit($form);

        self::assertNotOnPage('An error occurred');
        self::assertNotOnPage('entity.edit.error.publish');
    }

    /**
     * Regression test for issue #1379: when a test-env publish fails, only the error flash must
     * appear — not a success flash alongside it.
     */
    public function testFailedPublishShowsOnlyErrorFlash(): void
    {
        $this->testPublicationClient->registerPublishFailure();

        $crawler = self::$pantherClient->request('GET', "/entity/edit/test/{$this->rsManageId}/1");

        $form = $crawler->selectButton('Publish')->form();

        self::$pantherClient->submit($form);

        self::assertOnPage('Unable to publish the entity, try again later');
        self::assertNotOnPage('Your changes were saved');
    }
}
