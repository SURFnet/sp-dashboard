<?php

declare(strict_types = 1);

/**
 * Copyright 2017 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Application\Metadata\JsonGenerator;

use stdClass;
use Surfnet\ServiceProviderDashboard\Application\Service\AttributeServiceInterface;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use function array_diff;
use function array_keys;

/**
 * Builds ARP metadata for the JSON export.
 */
class ArpGenerator implements MetadataGenerator
{
    public function __construct(private readonly AttributeServiceInterface $attributeService)
    {
    }

    public function build(ManageEntity $entity): array
    {
        $attributes = [];
        $entityAttributes = $entity->getAttributes();
        foreach ($this->attributeService->getUrns() as $urn) {
            $attributeList = $entityAttributes->findAllByUrn($urn);
            // Only add the attributes with a motivation
            foreach ($attributeList as $attribute) {
                if (!$attribute || !$attribute->hasMotivation()) {
                    continue;
                }
                $attributes[$urn][] = [
                    'source' => $attribute->getSource(),
                    'value' => $attribute->getValue() === '' ? '*' : $attribute->getValue(),
                    'motivation' => $attribute->getMotivation(),
                ];
            }
        }

        $this->addManageOnlyAttributes($attributes, $entity);

        /**
         * If $attributes is empty, json-conversion will be to an empty array rather than to an empty object.
         * In the case of a non-empty array it'll be converted to an object.
         * We want both cases to be an object.
         * So if it's an empty array return a simple stdClass (which does get converted to an empty object)
         */
        if (empty($attributes)) {
            $attributes = new stdClass();
        }

        return [
            'attributes' => $attributes,
            'enabled' => true,
        ];
    }

    private function addManageOnlyAttributes(array &$attributes, ManageEntity $entity): void
    {
        $originalAttributes = $entity->getAttributes()->getOriginalAttributes();
        $spDashboardTracked = $this->attributeService->getUrns();
        $nonTrackedUrns = array_diff(array_keys($originalAttributes), $spDashboardTracked);

        foreach ($nonTrackedUrns as $urn) {
            $attributes[$urn] = [];
            foreach ($originalAttributes[$urn] as $attribute) {
                $attributes[$urn][] = [
                    'source' => $attribute->getSource(),
                    'value' => $attribute->getValue(),
                    'motivation' => $attribute->getMotivation(),
                ];
            }
        }
    }
}
