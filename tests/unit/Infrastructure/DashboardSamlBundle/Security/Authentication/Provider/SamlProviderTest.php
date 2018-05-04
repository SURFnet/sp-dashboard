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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Infrastructure\DashboardBundle\Mailer;

use InvalidArgumentException;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Surfnet\SamlBundle\SAML2\Attribute\AttributeDictionary;
use Surfnet\ServiceProviderDashboard\Domain\Repository\ContactRepository;
use Surfnet\ServiceProviderDashboard\Domain\Repository\ServiceRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Authentication\Provider\SamlProvider;

class SamlProviderTest extends TestCase
{
    /**
     * @dataProvider provideValidAdminTeams
     */
    public function test_administrator_teams_validation_accepts_valid_teams($validAdminTeams)
    {
        $this->assertInstanceOf(SamlProvider::class, $this->buildProvider($validAdminTeams));
    }

    /**
     * @dataProvider provideInvalidAdminTeams
     */
    public function test_administrator_teams_validation_rejects_invalid_teams($invalidAdminTeams)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('All entries in the `administrator_teams` config parameter should be string.');
        $this->buildProvider($invalidAdminTeams);
    }


    public function provideValidAdminTeams()
    {
        return [
            [['urn:collab:foo:team.foobar.com']],
            [['urn:collab:foo:team.foobar.com', 'urn:collab:foo:team.foobar.com']],
        ];
    }

    public function provideInvalidAdminTeams()
    {
        return [
            [['']],
            [[345345]],
            [[true, false]],
            [[['foo', 'bar']]],
        ];
    }

    private function buildProvider($administratorTeams)
    {
        $contactRepo = m::mock(ContactRepository::class);
        $serviceRepo = m::mock(ServiceRepository::class);
        $attributeDictionary = m::mock(AttributeDictionary::class);
        $logger = m::mock(LoggerInterface::class);

        return new SamlProvider($contactRepo, $serviceRepo, $attributeDictionary, $logger, $administratorTeams);
    }
}
