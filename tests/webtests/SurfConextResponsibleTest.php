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

use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\DataFixtures\ORM\WebTestFixtures;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

class SurfConextResponsibleTest extends WebTestCase
{
    private const INSTITUTION_ID = 'ACME Corporation';
    public function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures();

        $this->registerManageEntity(
            'test',
            'saml20_idp',
            '1d4abec3-3f67-4b8a-b90d-ce56a3b0abc5',
            'Test IdP',
            'test-idp-1',
            'https://test-idp/metadata',
            WebTestFixtures::TEAMNAME_ACME,
            self::INSTITUTION_ID,
        );
    }

    public function test_after_login_i_am_on_connections_page(): void
    {
        $this->logInSurfConextResponsible(self::INSTITUTION_ID);
        $url = self::$pantherClient->getCurrentURL();
        $urlParts = parse_url($url);
        self::assertEquals('/connections', $urlParts['path']);
        self::assertOnPage('No entities found'); // At this point there should be no entities
    }

    public function test_entities_are_listed_on_the_page_with_connected_idp(): void
    {
        $this->createSpEntity('ACME Anvil');
        $this->logInSurfConextResponsible(self::INSTITUTION_ID);
        $this->assertOnPage('ACME Anvil Name English');
        $this->assertOnPage('Test IdP Name Dutch');
        $this->assertVendorOnPage('Acme Corporation');
    }

    public function test_entities_are_listed_on_the_page_with_connected_idp_with_multiple_sps(): void
    {
        $this->createSpEntity('ACME Anvil 1');
        $this->createSpEntity('ACME Anvil 2');
        $this->createSpEntity('ACME Anvil 3');
        $this->createSpEntity('Should not be on page', WebTestFixtures::TEAMNAME_ACME, 'not-acme');

        $this->logInSurfConextResponsible(self::INSTITUTION_ID);
        $this->assertVendorOnPage('Acme Corporation');
        $this->assertOnPage('ACME Anvil 1 Name English');
        $this->assertOnPage('ACME Anvil 2 Name English');
        $this->assertOnPage('ACME Anvil 3 Name English');
        // The fourth SP should not show up on the page
        $this->assertNotOnPage('Should not be on page');
        $this->assertOnPage('Test IdP Name Dutch');
    }

    public function test_different_teams_same_institution_id(): void
    {
        $this->createSpEntity('ACME Anvil 1');
        $this->createSpEntity('ACME Anvil 2', WebTestFixtures::TEAMNAME_IBUILDINGS);
        $this->createSpEntity('ACME Anvil 3', WebTestFixtures::TEAMNAME_SURF);
        $this->createSpEntity('ACME for SURF', WebTestFixtures::TEAMNAME_SURF);

        $this->logInSurfConextResponsible(self::INSTITUTION_ID);
        $crawler = self::$pantherClient->refreshCrawler();
        $this->assertCount(4, $crawler->filter('tbody td.name')); // There should be 4 entities
        $vendors = $crawler->filter('tbody td.vendor');

        $this->assertCount(3, $vendors); // There should be 3 vendors (services) listed
        $this->assertVendorOnPage('SURFnet');
        $this->assertVendorOnPage('Ibuildings B.V.');
        $this->assertVendorOnPage('Acme Corporation');
    }

    public function test_administrator_does_not_have_access(): void
    {
        $this->createSpEntity('ACME Anvil');
        $this->logIn();
        self::$pantherClient->request('GET', '/connections');
        // Nasty trick to get the HTTP Response code (it is not available via the client response).
        // See: https://github.com/symfony/panther/issues/67
        $statusCode = self::$pantherClient->executeScript('return window.performance.getEntries()[0].responseStatus');
        $this->assertEquals(Response::HTTP_FORBIDDEN, $statusCode);
    }

    private function assertVendorOnPage(string $vendorName): void
    {
        $vendors = self::$pantherClient->refreshCrawler()->filter('tbody td.vendor');
        foreach ($vendors as $vendor) {
            if ($vendor->getText() === $vendorName) {
                return;
            }
        }
        $this->fail(sprintf('Vendor %s could not be found', $vendorName));
    }
    
    private function createSpEntity(
        string $nameEn,
        $teamName = WebTestFixtures::TEAMNAME_ACME,
        $institutionId = self::INSTITUTION_ID
    ): void {
        $hostname = urlencode(strtolower(str_replace(' ', '-', $nameEn)));
        $this->registerManageEntity(
            'test',
            'saml20_sp',
            Uuid::v4()->toRfc4122(),
            $nameEn,
            'https://' . $hostname,
            'https://' . $hostname . '/metadata',
            $teamName,
            $institutionId,
        );
    }
}
