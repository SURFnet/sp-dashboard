<?php

/**
 * Copyright 2020 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Domain\Entity\Entity;

use Exception;
use PHPUnit\Framework\TestCase;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Protocol;

class ProtocolTest extends TestCase
{
    /**
     * @dataProvider provideProtocolTestData
     */
    public function test_it_can_merge_data(Protocol $protocol, Protocol $newData, Protocol $expectation)
    {
        $protocol->merge($newData);

        if ($expectation !== null) {
            self::assertEquals($expectation->getProtocol(), $protocol->getProtocol());
        } else {
            self::assertNull($protocol->getProtocol());
        }
    }

    /**
     * @dataProvider provideProtocolManagedTestData
     */
    public function test_it_accepts_the_managed_protocol(Protocol $protocol, string $expectedProtocol)
    {
        self::assertEquals($expectedProtocol, $protocol->getManagedProtocol());
    }

    public function test_it_throws_exception_on_invalid_managed_protocol()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The protocol \'fantasy\' is not supported');
        $protocol = new Protocol('fantasy');
        $protocol->getManagedProtocol();
    }
    
    public function provideProtocolTestData()
    {
        yield [
            new Protocol('saml20'),
            new Protocol('saml20'),
            new Protocol('saml20'),
        ];
        yield [
            new Protocol('saml20'),
            new Protocol(null),
            new Protocol(null),
        ];
    }

    public function provideProtocolManagedTestData()
    {
        yield [
            new Protocol('saml20'),
            'saml20_sp'
        ];
        yield [
            new Protocol('oidcng'),
            'oidc10_rp',
        ];
        yield [
            new Protocol('oauth20_rs'),
            'oauth20_rs',
        ];
        yield [
            new Protocol('oauth20_ccc'),
            'oidc10_rp',
        ];
    }
}
