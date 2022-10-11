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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Infrastructure\DashboardBundle\Dto;

use PHPUnit\Framework\TestCase;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Dto\Attribute;

class AttributeTest extends TestCase
{
    public function test_it_is_created()
    {
        $attribute =   [
            'id' => 'emailAddress',
            'form' => [
                'translations' => [
                    'en' => [
                        'label' => 'emailAddressAttribute',
                        'info' => 'Description is placed here'
                    ]
                ]
            ],
            'urns' => [
                'urn:mace:dir:attribute-def:mail',
                'urn:oid:0.9.2342.19200300.100.1.3'
                ]
        ];
        $attributeDto = Attribute::fromAttribute($attribute);

        $this->assertEquals('emailAddress', $attributeDto->id);
        $this->assertIsArray($attributeDto->urns);
        $this->assertIsObject($attributeDto->form);
        $this->assertCount(2, $attributeDto->urns);
        $this->assertEquals('urn:mace:dir:attribute-def:mail', $attributeDto->urns[0]);
        $this->assertEquals('urn:oid:0.9.2342.19200300.100.1.3', $attributeDto->urns[1]);
    }

    public function test_exclude_on_entity_type()
    {
        $attribute =   [
            'id' => 'emailAddress',
            'form' => [
                'excludeOnEntityType' => ['oidcng'],
                'translations' => [
                    'en' => [
                        'label' => 'emailAddressAttribute',
                        'info' => 'Description is placed here'
                    ]
                ]
            ],
            'urns' => [
                'urn:mace:dir:attribute-def:mail',
                'urn:oid:0.9.2342.19200300.100.1.3'
            ]
        ];
        $attributeDto = Attribute::fromAttribute($attribute);

        $this->assertEquals('emailAddress', $attributeDto->id);
        $this->assertIsArray($attributeDto->urns);
        $this->assertIsObject($attributeDto->form);
        $this->assertIsArray($attributeDto->form->excludeFrom);
        $this->assertCount(1, $attributeDto->form->excludeFrom);
        $this->assertCount(2, $attributeDto->urns);
        $this->assertEquals('urn:mace:dir:attribute-def:mail', $attributeDto->urns[0]);
        $this->assertEquals('urn:oid:0.9.2342.19200300.100.1.3', $attributeDto->urns[1]);
    }
}
