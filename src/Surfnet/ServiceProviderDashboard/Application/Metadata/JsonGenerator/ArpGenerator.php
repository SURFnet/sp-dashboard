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

use Surfnet\ServiceProviderDashboard\Application\Dto\MetadataConversionDto;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Repository\AttributesMetadataRepository;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute;

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

    public function build(MetadataConversionDto $entity)
    {
        $attributes = [];

        foreach ($this->repository->findAll() as $definition) {
            $getterName = $definition->getterName;

            $attr = $entity->$getterName();

            if ($attr instanceof Attribute && $attr->isRequested()) {
                $urn = reset($definition->urns);
                $attributes[$urn] = [
                    [
                        'source' => 'idp',
                        'value' => '*',
                    ],
                ];

                if ($attr->hasMotivation()) {
                    $attributes[$urn][0]['motivation'] = $attr->getMotivation();
                }
                $this->mergeWithManageAttributes($attributes, $urn, $entity);
            }
        }

        $this->addManageOnlyAttributes($attributes, $entity);

        return [
            'attributes' => $attributes,
            'enabled' => true,
        ];
    }

    private function addManageOnlyAttributes(array &$attributes, MetadataConversionDto $entity)
    {
        $managedAttributeUrns = $this->repository->findAllAttributeUrns();

        if ($entity->isManageEntity()) {
            // Also add the attributes that are not managed in the SPD entity, but have been configured in Manage
            foreach ($entity->getArpAttributes()->getAttributes() as $manageAttribute) {
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

        if ($entity->getProtocol() === Constants::TYPE_OPENID_CONNECT_TNG) {
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

    /**
     * Preserves Manage attribute data, which would otherwise be reset to '*' and 'idp'
     *
     * @param array $attributes
     * @param string $urn
     * @param MetadataConversionDto $entity
     */
    private function mergeWithManageAttributes(array &$attributes, $urn, MetadataConversionDto $entity)
    {
        if ($entity->isManageEntity() && $entity->getArpAttributes()->findByUrn($urn)) {
            $manageAttribute = $entity->getArpAttributes()->findByUrn($urn);
            $attributes[$urn][0]['source'] = $manageAttribute->getSource();
            $attributes[$urn][0]['value'] = $manageAttribute->getValue();
        }
    }
}
