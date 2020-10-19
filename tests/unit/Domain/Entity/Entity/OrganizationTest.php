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
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Organization;

class OrganizationTest extends TestCase
{
    /**
     * @dataProvider provideOrganizationTestData
     */
    public function test_it_can_merge_data(Organization $organization, Organization $newData, Organization $expectation)
    {
        $organization->merge($newData);

        self::assertEquals($expectation->getNameEn(), $organization->getNameEn());
        self::assertEquals($expectation->getDisplayNameEn(), $organization->getDisplayNameEn());
        self::assertEquals($expectation->getUrlEn(), $organization->getUrlEn());
        self::assertEquals($expectation->getNameNl(), $organization->getNameNl());
        self::assertEquals($expectation->getDisplayNameNl(), $organization->getDisplayNameNl());
        self::assertEquals($expectation->getUrlNl(), $organization->getUrlNl());
    }

    public function provideOrganizationTestData()
    {
        yield [
            $this->organization('organization a'),
            $this->organization('organization a'),
            $this->organization('organization a'),
        ];
        yield [
            $this->organization('organization a'),
            $this->organization('organization b'),
            $this->organization('organization b'),
        ];
        yield [
            $this->organization('organization a'),
            $this->organization(null),
            $this->organization(null),
        ];
    }

    private function organization(?string $organizationName)
    {
        if ($organizationName === null) {
            return new Organization(null, null, null, null, null, null);
        }
        return new Organization(
            $organizationName . " EN",
            $organizationName . " Display EN",
            $organizationName . " Url EN",
            $organizationName . " NL",
            $organizationName . " Display NL",
            $organizationName . " Url NL"
        );
    }
}
