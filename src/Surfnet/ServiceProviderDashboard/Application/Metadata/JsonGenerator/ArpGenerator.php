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

use DateTime;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
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

    public function build(Entity $entity)
    {
        $attributes = [];

        foreach ($this->repository->findAll() as $definition) {
            $getterName = $definition->getterName;

            // Skip attributes we know about but don't have registered.
            if (!method_exists($entity, $getterName)) {
                continue;
            }

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
            }
        }

        return [
            'attributes' => $attributes,
            'enabled' => true,
        ];
    }
}
