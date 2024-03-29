<?php

declare(strict_types = 1);

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

use Surfnet\ServiceProviderDashboard\Domain\Entity\Comparable;

class AttributeList implements Comparable
{
    /**
     * @var Attribute[]
     */
    private array $attributes = [];

    private array $originalAttributes = [];

    public static function fromApiResponse(array $data): self
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

    public function add(Attribute $attribute): void
    {
        $this->attributes[$attribute->getName()][] = $attribute;
    }

    /**
     * Returns the first attribute with a motivation
     *
     * If not a single motivation is present, it returns the first attribute.
     */
    public function findByUrn(string $urn): ?Attribute
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
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @return Attribute[]
     */
    public function getOriginalAttributes(): array
    {
        return $this->originalAttributes;
    }

    private function clear(): void
    {
        $this->attributes = [];
    }

    public function merge(AttributeList $attributes): void
    {
        $this->originalAttributes = $this->attributes;
        $this->clear();
        foreach ($attributes->getAttributes() as $urn => $attributeList) {
            // If the attribute was not yet created, use the new value
            $manageAttribute = $attributes->getAttributes()[$urn];

            // Else, grab the matching original manage attribute set
            if (array_key_exists($urn, $this->originalAttributes)) {
                $manageAttribute = $this->originalAttributes[$urn];
            }

            $newMotivation = '';
            foreach ($attributeList as $attr) {
                if ($attr->hasMotivation()) {
                    $newMotivation = $attr->getMotivation();
                }
            }
            /**
 * @var Attribute $attribute
*/
            foreach ($manageAttribute as $attribute) {
                $attribute = $attribute->updateMotivation($newMotivation);
                $this->add($attribute);
            }
        }
    }

    public function asArray(): array
    {
        $attributes = [];
        foreach ($this->attributes as $attribute) {
            $attribute = reset($attribute);
            $urn = $attribute->getName();
            $attributes[$urn][] = [
                'source' => $attribute->getSource(),
                'value' => $attribute->getValue(),
                'motivation' => $attribute->getMotivation(),
            ];
        }
        if ($attributes !== []) {
            return [
                'arp' => [
                    'attributes' => $attributes,
                    'enabled' => true,
                ],
            ];
        }
        return [];
    }
}
