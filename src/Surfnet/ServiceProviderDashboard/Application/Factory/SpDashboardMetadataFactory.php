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

namespace Surfnet\ServiceProviderDashboard\Application\Factory;

use DateTime;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Repository\AttributesMetadataRepository;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute;

/**
 * Adds the team name original metadata url to a list of attributes that can be stored in the serivce registry (Manage)
 */
class SpDashboardMetadataFactory implements MetadataFactory
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
        $spDashboardAttributes = $this->repository->findAllSpDashboardAttributes();
        $attributes = [];

        foreach ($spDashboardAttributes as $attribute) {
            // Get the associated getter
            $getterName = $attribute->getterName;

            switch (true) {
                case $attribute->id == 'teamID':
                    $service = $entity->getService();
                    if (method_exists($service, $getterName) && !empty($service->$getterName())) {
                        $attributes[self::METADATA_PREFIX . $attribute->urns[0]] = $service->$getterName();
                    }
                    break;
                case $attribute->id == 'originalMetadataUrl':
                    if (method_exists($entity, $getterName) && !empty($entity->$getterName())) {
                        $attributes[self::METADATA_PREFIX . $attribute->urns[0]] = $entity->$getterName();
                    }
                    break;
            }
        }
        return $attributes;
    }
}
