<?php

/**
 * Copyright 2024 SURFnet B.V.
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

class SurfConextResponsibleTest extends WebTestCase
{
    private string $institutionId = 'ACME Corporation';
    public function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures();
        $this->teamsQueryClient->registerTeam('demo:openconext:org:acme.nl', 'data');
    }

    public function test_after_login_i_am_on_connections_page()
    {
        $this->logInSurfConextResponsible($this->institutionId);
        $url = self::$pantherClient->getCurrentURL();
        $urlParts = parse_url($url);
        self::assertEquals('/connections', $urlParts['path']);
        self::assertOnPage('No entities found'); // At this point there should be no entities
    }

    public function test_entities_are_listed_on_the_page()
    {
        $this->registerManageEntity(
            'test',
            'saml20_sp',
            'aee8f00d-428a-4fbc-9cf8-ad2f3b2af589',
            'ACME Anvil',
            'http://acme-anvil',
            'https://acme-anvil.example.com/metadata',
            'demo:openconext:org:acme.nl',
            $this->institutionId,
        );
        $this->logInSurfConextResponsible($this->institutionId);
        $this->assertOnPage('ACME Anvil Name English');
        // When logging in with only the SURF representative, we do not know the service the entity is associated with
        $this->assertOnPage('Unknown service name');
    }

    public function test_entities_are_listed_on_the_page_with_connected_idp()
    {
        $this->registerManageEntity(
            'test',
            'saml20_sp',
            'aee8f00d-428a-4fbc-9cf8-ad2f3b2af589',
            'ACME Anvil',
            'http://acme-anvil',
            'https://acme-anvil.example.com/metadata',
            'demo:openconext:org:acme.nl',
            $this->institutionId,
        );
        $this->registerManageEntity(
            'test',
            'saml20_idp',
            '1d4abec3-3f67-4b8a-b90d-ce56a3b0abc5',
            'Test IdP',
            'test-idp-1',
            'https://test-idp/metadata',
            'demo:openconext:org:acme.nl',
            $this->institutionId,
        );
        $this->logInSurfConextResponsible($this->institutionId);
        $this->assertOnPage('ACME Anvil Name English');
        $this->assertOnPage('Test IdP Name Dutch');
    }

    public function test_entities_are_listed_on_the_page_with_connected_idp_with_multiple_sps()
    {
        $this->registerManageEntity(
            'test',
            'saml20_sp',
            'aee8f00d-428a-4fbc-9cf8-ad2f3b2af589',
            'ACME Anvil 1',
            'http://acme-anvil-1',
            'https://acme-anvil.example.com/metadata',
            'demo:openconext:org:acme.nl',
            $this->institutionId,
        );
        $this->registerManageEntity(
            'test',
            'saml20_sp',
            'bee8f00d-428a-4fbc-9cf8-ad2f3b2af589',
            'ACME Anvil 2',
            'http://acme-anvil-2',
            'https://acme-anvil.example.com/metadata',
            'demo:openconext:org:acme.nl',
            $this->institutionId,
        );
        $this->registerManageEntity(
            'test',
            'saml20_sp',
            'cee8f00d-428a-4fbc-9cf8-ad2f3b2af589',
            'ACME Anvil 3',
            'http://acme-anvil-3',
            'https://acme-anvil.example.com/metadata',
            'demo:openconext:org:acme.nl',
            $this->institutionId,
        );
        $this->registerManageEntity(
            'test',
            'saml20_sp',
            'fee8f00d-428a-4fbc-9cf8-ad2f3b2af589',
            'Should not be on page',
            'http://foobar',
            'https://foobar.example.com/metadata',
            'demo:openconext:org:acme.nl',
            'not-acme',
        );
        $this->registerManageEntity(
            'test',
            'saml20_idp',
            '1d4abec3-3f67-4b8a-b90d-ce56a3b0abc5',
            'Test IdP',
            'test-idp-1',
            'https://test-idp/metadata',
            'demo:openconext:org:acme.nl',
            $this->institutionId,
        );
        $this->logInSurfConextResponsible($this->institutionId);
        $this->assertOnPage('ACME Anvil 1 Name English');
        $this->assertOnPage('ACME Anvil 2 Name English');
        $this->assertOnPage('ACME Anvil 3 Name English');
        // The fourth SP should not show up on the page
        $this->assertNotOnPage('Should not be on page');
        $this->assertOnPage('Test IdP Name Dutch');
    }
}
