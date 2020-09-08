<?php

/**
 * Copyright 2018 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;

class AttributeList
{
    /**
     * @var Attribute[]
     */
    private $attributes = [];

    public static function fromApiResponse(array $data)
    {
        $list = new self();

        if (isset($data['data']['arp'])) {
            $attributes = $data['data']['arp']['attributes'];

            foreach ($attributes as $attributeName => $attributeList) {
                foreach ($attributeList as $attributeData) {
                    $list->add(Attribute::fromApiResponse($attributeName, $attributeData));
                }
            }
        }

        return $list;
    }

    public function add(Attribute $attribute)
    {
        $this->attributes[$attribute->getName()] = $attribute;
    }

    /**
     * @param $urn
     * @return Attribute|null
     */
    public function findByUrn($urn)
    {
        if (isset($this->attributes[$urn])) {
            return $this->attributes[$urn];
        }
        return null;
    }

    /**
     * @return Attribute[]
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    private function clear()
    {
        $this->attributes = [];
    }

    public function merge(AttributeList $attributes)
    {
        $this->clear();
        foreach ($attributes->getAttributes() as $attribute) {
            $this->add($attribute);
        }
    }
}
