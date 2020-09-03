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
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Coin;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Logo;

class CoinTest extends TestCase
{
    /**
     * @dataProvider provideCoinTestData
     */
    public function test_it_can_merge_data(Coin $coin, ?Coin $newData, ?Coin $expectation)
    {
        $coin->merge($newData);
        if ($expectation !== null) {
            self::assertEquals($expectation->getSignatureMethod(), $coin->getSignatureMethod());
            self::assertEquals($expectation->getServiceTeamId(), $coin->getServiceTeamId());
            self::assertEquals($expectation->getOriginalMetadataUrl(), $coin->getOriginalMetadataUrl());
            self::assertEquals($expectation->getExcludeFromPush(), $coin->getExcludeFromPush());
            self::assertEquals($expectation->getApplicationUrl(), $coin->getApplicationUrl());
            self::assertEquals($expectation->getEula(), $coin->getEula());
            self::assertEquals($expectation->getOidcClient(), $coin->getOidcClient());
        } else {
            self::assertNull($coin->getSignatureMethod());
            self::assertNull($coin->getServiceTeamId());
            self::assertNull($coin->getOriginalMetadataUrl());
            self::assertNull($coin->getExcludeFromPush());
            self::assertNull($coin->getApplicationUrl());
            self::assertNull($coin->getEula());
            self::assertNull($coin->getOidcClient());
        }
    }

    public function provideCoinTestData()
    {
        yield [
            new Coin('signatureMethod', '23', 'https://www.example.com', '1', 'https://example.com', 'https://example.com/eula', 1),
            new Coin('signatureMethod', '23', 'https://www.example.com', '1', 'https://example.com', 'https://example.com/eula', 1),
            new Coin('signatureMethod', '23', 'https://www.example.com', '1', 'https://example.com', 'https://example.com/eula', 1)
        ];
        yield [
            new Coin('signatureMethod', '23', 'https://www.example.com', '1', 'https://example.com', 'https://example.com/eula', 1),
            null,
            new Coin(null, null, null, null, null, null, null)
        ];
        yield [
            new Coin('signatureMethod', '23', 'https://www.example.com', '1', 'https://example.com', 'https://example.com/eula', 1),
            new Coin('signatureMethod', null, 'https://www.example.com', null, 'https://example.com', 'https://example.com/eula', 1),
            new Coin('signatureMethod', null, 'https://www.example.com', null, 'https://example.com', 'https://example.com/eula', 1),
        ];
        yield [
            new Coin('signatureMethod', '23', 'https://www.example.com', '1', 'https://example.com', 'https://example.com/eula', 1),
            new Coin(null, null, null, null, null, null, null),
            new Coin(null, null, null, null, null, null, null)
        ];
    }
}
