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
    public function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures();
        $this->teamsQueryClient->registerTeam('demo:openconext:org:surf.nl', 'data');
        $this->logInSurfConextResponsible('ACME Corporation');
    }

    public function test_after_login_i_am_on_connections_page()
    {
        $crawler = self::$pantherClient->getCrawler();
        self::assertEquals('/connections', $crawler->getBaseHref());
    }
}
