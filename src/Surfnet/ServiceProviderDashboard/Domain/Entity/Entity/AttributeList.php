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

use function reset;

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
        $this->attributes[$attribute->getName()][] = $attribute;
    }

    /**
     * Returns the first attribute with a motivation
     *
     * If not a single motivation is present, it returns the first attribute.
     *
     * @param $urn
     * @return Attribute|null
     */
    public function findByUrn($urn)
    {
        if (isset($this->attributes[$urn])) {
            if (count($this->attributes[$urn]) > 1) {
                // Look for the attribute with a motivation, if it is not found, return the first in line
                foreach ($this->attributes[$urn] as $attribute) {
                    if ($attribute->hasMotivation()) {
                        return $attribute;
                    }
                }
            }
            // One attribute on this attr, return it without it being wrapped in an array
            return reset($this->attributes[$urn]);
        }
        return null;
    }

    public function findAllByUrn(string $urn): array
    {
        $attributes = [];
        foreach ($this->getAttributes() as $attributeCandidates) {
            foreach ($attributeCandidates as $attributeCandidate) {
                if ($attributeCandidate->getName() === $urn) {
                    $attributes[] = $attributeCandidate;
                }
            }
        }
        return $attributes;
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
        $originalAttributes = $this->attributes;
        $this->clear();
        foreach ($attributes->getAttributes() as $urn => $attributeList) {
            // Grab the matching original manage attribute set
            $manageAttribute = $originalAttributes[$urn];
            $newMotivation = '';
            foreach ($attributeList as $attr) {
                if ($attr->hasMotivation()) {
                    $newMotivation = $attr->getMotivation();
                }
            }
            /** @var Attribute $attribute */
            foreach ($manageAttribute as $attribute) {
                $attribute = $attribute->updateMotivation($newMotivation);
                $this->add($attribute);
            }
        }
    }
}
