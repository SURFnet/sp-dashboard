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
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\TypeOfService;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\TypeOfServiceCollection;

class CoinTest extends TestCase
{
    public function test_type_of_service_data_is_parsed_correctly()
    {
        $enTypes = 'Productivity,Management of education/research,Medical,eCommerce';
        // to make a point. The Dutch translations are following the configured translations. Not the ones provided from Manage
        $nlTypes = 'Productiviteit,matcht niet,Mecisch,eCommerce';
        $data = sprintf(file_get_contents(__DIR__.'/fixture/read_response-coin-types-of-service.json'), $enTypes, $nlTypes);
        $decodedData = json_decode($data, true);
        $coin = Coin::fromApiResponse($decodedData['metaDataFields']);
        $tos = $coin->getTypeOfService();
        self::assertContainsOnlyInstancesOf(TypeOfService::class, $tos->getArray());
        self::assertEquals($enTypes, $tos->getServicesAsEnglishString());
        self::assertEquals(
            'Productiviteit,Organisatie van onderwijs/onderzoek,Medisch,eCommerce',
            $tos->getServicesAsDutchString()
        );
    }

    /**
     * @dataProvider provideCoinTestData
     */
    public function test_it_can_merge_data(Coin $coin, Coin $newData, Coin $expectation)
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
            new Coin('signatureMethod', '23', 'https://www.example.com', '1', 'https://example.com', new TypeOfServiceCollection(), 'https://example.com/eula', 1, null, true),
            new Coin('signatureMethod', '23', 'https://www.example.com', '1', 'https://example.com', new TypeOfServiceCollection(), 'https://example.com/eula', 1, null, true),
            new Coin('signatureMethod', '23', 'https://www.example.com', '1', 'https://example.com', new TypeOfServiceCollection(), 'https://example.com/eula', 1, null, true)
        ];
        yield [
            new Coin('signatureMethod', '23', 'https://www.example.com', '1', 'https://example.com', new TypeOfServiceCollection(), 'https://example.com/eula', 1, null, true),
            new Coin('signatureMethod', null, 'https://www.example.com', null, 'https://example.com', new TypeOfServiceCollection(), 'https://example.com/eula', 1, null, null),
            new Coin('signatureMethod', null, 'https://www.example.com', '1', 'https://example.com', new TypeOfServiceCollection(), 'https://example.com/eula', 1, null, null),
        ];
        yield [
            new Coin('signatureMethod', '23', 'https://www.example.com', '1', 'https://example.com', new TypeOfServiceCollection(), 'https://example.com/eula', 1, null, false),
            new Coin(null, null, null, null, null, new TypeOfServiceCollection(), null, null, null, false),
            new Coin(null, null, null, '1', null, new TypeOfServiceCollection(), null, null, null, false)
        ];
    }
    public function provideCoinTestDataWithTypeOfService()
    {
        yield [

        ];
    }
}
