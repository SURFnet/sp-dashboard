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

    public function provideProtocolTestData()
    {
        yield [
            new Protocol('saml20'),
            new Protocol('saml20'),
            new Protocol('saml20'),
        ];
        yield [
            new Protocol('oidc'),
            new Protocol('oidcng'),
            new Protocol('oidcng')
        ];
        yield [
            new Protocol('saml20'),
            new Protocol(null),
            new Protocol(null),
        ];
    }
}
