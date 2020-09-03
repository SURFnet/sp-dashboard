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
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Attribute;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\AttributeList;

class AttributeListTest extends TestCase
{
    /**
     * @dataProvider provideAttributeListTestData
     */
    public function test_it_can_merge_data(AttributeList $list, ?AttributeList $newData, AttributeList $expectations)
    {
        $list->merge($newData);
        $expectedAttributes = $expectations->getAttributes();
        $expectedUrns = array_keys($expectedAttributes);

        $actualAttributes = $list->getAttributes();
        $actualUrns = array_keys($actualAttributes);

        self::assertEquals($expectedUrns, $actualUrns);
        foreach ($actualAttributes as $attribute) {
            $expectedAttribute = $expectations->findByUrn($attribute->getName());
            // Verify the attribute values have been updated correctly
            self::assertEquals($expectedAttribute->getName(), $attribute->getName());
            self::assertEquals($expectedAttribute->getMotivation(), $attribute->getMotivation());
            self::assertEquals($expectedAttribute->getValue(), $attribute->getValue());
            self::assertEquals($expectedAttribute->getSource(), $attribute->getSource());
        }
    }

    public function provideAttributeListTestData()
    {

        yield [
            $this->attributeList(
                [
                    $this->attr('urn0', '*', 'idp', 'motivation'),
                    $this->attr('urn1', '*', 'idp', 'motivation'),
                    $this->attr('urn2', '*', 'idp', 'motivation')
                ]
            ),
            $this->attributeList(
                [
                    $this->attr('urn0', '*', 'idp', 'motivation'),
                    $this->attr('urn1', '*', 'idp', 'motivation'),
                ]
            ),
            $this->attributeList(
                [
                    $this->attr('urn0', '*', 'idp', 'motivation'),
                    $this->attr('urn1', '*', 'idp', 'motivation'),
                ]
            ),
        ];
        yield [
            $this->attributeList(
                [
                    $this->attr('urn0', '*', 'idp', 'motivation'),
                    $this->attr('urn1', '*', 'idp', 'motivation'),
                    $this->attr('urn2', '*', 'idp', 'motivation')
                ]
            ),
            $this->attributeList(
                [
                    $this->attr('urn2', '*', 'idp', 'motivation'),
                    $this->attr('urn1', '*', 'idp', 'super motivation'),
                ]
            ),
            $this->attributeList(
                [
                    $this->attr('urn2', '*', 'idp', 'motivation'),
                    $this->attr('urn1', '*', 'idp', 'super motivation'),
                ]
            ),
        ];
        yield [
            $this->attributeList(
                [
                    $this->attr('urn0', '*', 'idp', 'motivation'),
                    $this->attr('urn1', '*', 'idp', 'motivation'),
                    $this->attr('urn2', '*', 'idp', 'motivation')
                ]
            ),
            $this->attributeList([]),
            $this->attributeList([]),
        ];
    }

    private function attributeList(array $attributes = null)
    {
        $attributeList = new AttributeList();
        if ($attributes) {
            foreach ($attributes as $attribute) {
                $attributeList->add($attribute);
            }
        }
        return $attributeList;
    }

    private function attr(string $urn, string $value, string $source, string $motivation)
    {
        return new Attribute($urn, $value, $source, $motivation);
    }
}
