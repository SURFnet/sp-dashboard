<?php
/**
 * Copyright 2022 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Infrastructure\DashboardBundle\Repository;

use PHPUnit\Framework\TestCase;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Repository\AttributeRepository;

class AttributeRepositoryTest extends TestCase
{
    public function test_it_loads_attributes()
    {
        $attributeRepository = new AttributeRepository(__DIR__ . '/Fixtures/attributes.json');
        $results = $attributeRepository->findAll();
        $this->assertIsArray($results);
        $this->assertCount(16, $results);
    }

    public function test_it_loads_empty_attributes()
    {
        $attributeRepository = new AttributeRepository(__DIR__ . '/Fixtures/attributes-empty.json');
        $results = $attributeRepository->findAll();
        $this->assertEmpty($results);
    }

    public function test_it_has_all_attributes()
    {
        $expectedAttributes = [
            "displayName",
            "affiliation",
            "scopedaffiliation",
            "emailAddress",
            "commonName",
            "organization",
            "organizationType",
            "organizationUnit",
            "surName",
            "givenName",
            "entitlement",
            "uid",
            "principleName",
            "preferredLanguage",
            "personalCode",
            "eduPersonTargetedID",
        ];
        $attributeRepository = new AttributeRepository(__DIR__ . '/Fixtures/attributes.json');
        $attributes = $attributeRepository->findAll();

        foreach ($attributes as $attribute) {
            $this->assertContains($attribute->id, $expectedAttributes);
        }
    }
}
