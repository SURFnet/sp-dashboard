<?php

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
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\Repository\AttributesMetadataRepository;
use function array_diff;
use function array_intersect;
use function array_keys;

/**
 * Builds ARP metadata for the JSON export.
 */
class ArpGenerator implements MetadataGenerator
{
    /**
     * @var AttributesMetadataRepository
     */
    private $repository;

    public function __construct(AttributesMetadataRepository $repository)
    {
        $this->repository = $repository;
    }

    public function build(ManageEntity $entity): array
    {
        $attributes = [];
        $entityAttributes = $entity->getAttributes();
        foreach ($this->repository->findAll() as $definition) {
            $urn = reset($definition->urns);
            $attrributeList = $entityAttributes->findAllByUrn($urn);
            // Only add the attributes with a motivation
            foreach ($attrributeList as $attribute) {
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

    private function addManageOnlyAttributes(array &$attributes, ManageEntity $entity)
    {
        $spDashboardTracked = $this->repository->findAllAttributeUrns();
        $originalAttributes = $entity->getAttributes()->getOriginalAttributes();
        $nonTrackedUrns = array_diff(array_keys($originalAttributes), $spDashboardTracked);

        foreach ($nonTrackedUrns as $urn) {
            $attributes[$urn] = [];
            foreach ($originalAttributes[$urn] as $attribute) {
                $attributes[$urn][] = [
                    'source' => $attribute->getSource(),
                    'value' => $attribute->getValue(),
                    'motivation' => $attribute->getMotivation()
                ];
            }
        }

        if ($entity->getProtocol()->getProtocol() === Constants::TYPE_OPENID_CONNECT_TNG) {
            // The EPTI is to be added to the ARP invisibly. See: https://www.pivotaltracker.com/story/show/167511328
            // The user cannot configure EPTI @ ARP settings but the value is used internally.
            $attributes['urn:mace:dir:attribute-def:eduPersonTargetedID'] = [
                [
                    'source' => 'idp',
                    'value' => '*',
                    'motivation' => 'OIDC requires EduPersonTargetedID by default',
                ]
            ];
        }
    }
}
