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

use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\Repository\AttributesMetadataRepository;

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
            $attrribute = $entityAttributes->findByUrn($urn);
            // Only add the attributes with a motivation
            if (!$attrribute || !$attrribute->hasMotivation()) {
                continue;
            }
            if ($attrribute) {
                $attributes[$urn] = [
                    [
                        'source' => $attrribute->getSource(),
                        'value' => '*',
                        'motivation' => $attrribute->getMotivation(),
                    ],
                ];
            }
        }

        $this->addManageOnlyAttributes($attributes, $entity);

        return [
            'attributes' => $attributes,
            'enabled' => true,
        ];
    }

    private function addManageOnlyAttributes(array &$attributes, ManageEntity $entity)
    {
        $managedAttributeUrns = $this->repository->findAllAttributeUrns();

        if ($entity->isManageEntity()) {
            // Also add the attributes that are not managed in the SPD entity, but have been configured in Manage
            foreach ($entity->getAttributes()->getAttributes() as $manageAttribute) {
                if (!array_key_exists($manageAttribute->getName(), $attributes) && !in_array($manageAttribute->getName(), $managedAttributeUrns)) {
                    $attributes[$manageAttribute->getName()] = [
                        [
                            'source' => $manageAttribute->getSource(),
                            'value' => $manageAttribute->getValue(),
                        ]
                    ];
                    if (!empty($manageAttribute->getMotivation())) {
                        $attributes[$manageAttribute->getName()][0]['motivation'] = $manageAttribute->getMotivation();
                    }
                }
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
