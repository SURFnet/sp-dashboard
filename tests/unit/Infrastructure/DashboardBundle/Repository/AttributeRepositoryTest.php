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
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Dto\Attribute;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Dto\NullAttribute;
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

    public function test_it_can_find_attribute_by_urn()
    {
        $attributeRepository = new AttributeRepository(__DIR__ . '/Fixtures/attributes.json');
        $result = $attributeRepository->findOneByName('urn:mace:dir:attribute-def:eduPersonTargetedID');
        $this->assertInstanceOf(Attribute::class, $result);
    }

    public function test_it_returns_null_object_when_attribute_by_urn_yields_no_result()
    {
        $attributeRepository = new AttributeRepository(__DIR__ . '/Fixtures/attributes.json');
        $result = $attributeRepository->findOneByName('urn:mace:dir:attribute-def:eduPersonTargetedIdentification');
        $this->assertInstanceOf(NullAttribute::class, $result);
    }

    public function test_it_has_all_attributes()
    {
        $expectedAttributes = [
            "displayName",
            "affiliation",
            "scopedAffiliation",
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

    public function test_it_is_an_attribute()
    {
        $expectedAttributes = [
            "displayNameAttribute",
            "affiliationAttribute",
            "scopedAffiliationAttribute",
            "emailAddressAttribute",
            "commonNameAttribute",
            "organizationAttribute",
            "organizationTypeAttribute",
            "organizationUnitAttribute",
            "surNameAttribute",
            "givenNameAttribute",
            "entitlementAttribute",
            "uidAttribute",
            "principleNameAttribute",
            "preferredLanguageAttribute",
            "personalCodeAttribute",
            "eduPersonTargetedIDAttribute",
        ];

        $attributeRepository = new AttributeRepository(__DIR__ . '/Fixtures/attributes.json');
        foreach ($expectedAttributes as $name) {
            $this->assertTrue($attributeRepository->isAttributeName($name));
        }
    }

    public function test_it_is_not_an_attribute()
    {
        $expectedAttributes = [
            "displayNamesAttribute",
            "fantasyAttribute",
            "",
            "[]",
            ".*",
        ];

        $attributeRepository = new AttributeRepository(__DIR__ . '/Fixtures/attributes.json');
        foreach ($expectedAttributes as $name) {
            $this->assertFalse($attributeRepository->isAttributeName($name));
        }
    }
}
