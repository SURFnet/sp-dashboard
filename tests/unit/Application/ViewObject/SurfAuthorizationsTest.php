<?php
declare(strict_types = 1);
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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Application\ViewObject;

use Generator;
use PHPUnit\Framework\TestCase;
use Surfnet\ServiceProviderDashboard\Domain\Exception\InvalidAuthorizationException;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\SurfAuthorizations;

class SurfAuthorizationsTest extends TestCase
{

    /**
     * @dataProvider provideValidAttributeValues
     */
    public function test_create_authorizations(
        string $expectedOrganizationCode,
        bool $isSurfConextResponsible,
        array $attributeValues
    ) {
        $authorizations = new SurfAuthorizations(
            $attributeValues,
            'urn:mace:surfnet.nl:surfnet.nl:sab:role:SURFconext-verantwoordelijke'
        );
        self::assertEquals($isSurfConextResponsible, $authorizations->isSurfConextResponsible());
        self::assertEquals($expectedOrganizationCode, $authorizations->getOrganizationCode());
    }

    /**
     * @dataProvider provideInValidAttributeValues
     */
    public function test_create_invalid_authorizations(
        array $attributeValues
    ) {
        self::expectException(InvalidAuthorizationException::class);
        new SurfAuthorizations(
            $attributeValues,
            'urn:mace:surfnet.nl:surfnet.nl:sab:role:SURFconext-verantwoordelijke'
        );
    }

    public function provideValidAttributeValues(): Generator {
        yield ['1234', false, ["urn:mace:surfnet.nl:surfnet.nl:sab:organizationCode:1234","urn:mace:surfnet.nl:surfnet.nl:sab:role:soigneur"]];
        yield ['ab-ab-ab-ab', true, ["urn:mace:surfnet.nl:surfnet.nl:sab:organizationCode:ab-ab-ab-ab","urn:mace:surfnet.nl:surfnet.nl:sab:role:SURFconext-verantwoordelijke"]];
        yield ['HARDERWIJK', true, ["urn:mace:surfnet.nl:surfnet.nl:sab:organizationCode:HARDERWIJK","urn:mace:surfnet.nl:surfnet.nl:sab:role:SURFconext-verantwoordelijke","urn:mace:surfnet.nl:surfnet.nl:sab:role:admin"]];
    }

    public function provideInValidAttributeValues(): Generator {
        yield [["urn:mace:surfnet.nl:surfnet.nl:sab:organizationCode1234"]];
        yield [["urn:mace:surfnet.nl:surfnet.nl:sab:organizationCood:1234"]];
        yield [["urn:mace:surfnetnl:surfnet.nl:sab:organizationCode:1234"]];
    }
}
