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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Infrastructure\DashboardBundle\Service;

use PHPUnit\Framework\TestCase;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\HostBlocklistChecker;

class HostBlocklistCheckerTest extends TestCase
{
    public function testPublicIpIsAllowed(): void
    {
        $checker = new HostBlocklistChecker();

        $this->assertFalse($checker->isBlocked('https://93.184.216.34/metadata'));
    }

    public function testLoopbackIpIsBlocked(): void
    {
        $checker = new HostBlocklistChecker();

        $this->assertTrue($checker->isBlocked('http://127.0.0.1/metadata'));
    }

    public function testRfc1918RangeIsBlocked(): void
    {
        $checker = new HostBlocklistChecker();

        $this->assertTrue($checker->isBlocked('http://10.1.2.3/metadata'));
    }

    public function testCloudMetadataEndpointIsBlockedViaExplicitCidr(): void
    {
        $checker = new HostBlocklistChecker(['169.254.169.254/32']);

        $this->assertTrue($checker->isBlocked('http://169.254.169.254/latest/meta-data/'));
    }

    public function testPublicIpInExplicitDenylistIsBlocked(): void
    {
        $checker = new HostBlocklistChecker(['198.51.100.0/24']);

        $this->assertTrue($checker->isBlocked('http://198.51.100.42/metadata'));
    }

    public function testPublicIpOutsideDenylistIsNotBlocked(): void
    {
        $checker = new HostBlocklistChecker(['198.51.100.0/24']);

        $this->assertFalse($checker->isBlocked('http://93.184.216.34/metadata'));
    }

    public function testUrlWithoutHostIsBlocked(): void
    {
        $checker = new HostBlocklistChecker();

        $this->assertTrue($checker->isBlocked('not-a-url'));
    }

    public function testResolveReturnsLiteralIpUnchanged(): void
    {
        $checker = new HostBlocklistChecker();

        $this->assertEquals('93.184.216.34', $checker->resolve('93.184.216.34'));
    }

    public function testIsIpBlockedReturnsTrueForNull(): void
    {
        $checker = new HostBlocklistChecker();

        $this->assertTrue($checker->isIpBlocked(null));
    }

    public function testIsIpBlockedMatchesIsBlockedForTheSameHost(): void
    {
        $checker = new HostBlocklistChecker(['198.51.100.0/24']);

        $this->assertTrue($checker->isIpBlocked($checker->resolve('127.0.0.1')));
        $this->assertTrue($checker->isIpBlocked($checker->resolve('198.51.100.42')));
        $this->assertFalse($checker->isIpBlocked($checker->resolve('93.184.216.34')));
    }
}
