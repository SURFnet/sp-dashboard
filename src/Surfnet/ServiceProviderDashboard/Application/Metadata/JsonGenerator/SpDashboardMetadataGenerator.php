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

use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\Repository\AttributesMetadataRepository;

/**
 * Adds the team name original metadata url to a list of attributes that can be stored in the serivce registry (Manage)
 */
class SpDashboardMetadataGenerator implements MetadataGenerator
{
    public function __construct(private readonly AttributesMetadataRepository $repository)
    {
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function build(ManageEntity $entity): array
    {
        $spDashboardAttributes = $this->repository->findAllSpDashboardAttributes();
        $attributes = [];

        foreach ($spDashboardAttributes as $attribute) {
            // Get the associated getter
            $getterName = $attribute->getterName;
            switch (true) {
                case $attribute->id === 'teamID':
                    $service = $entity->getService();
                    if (method_exists($service, $getterName) && !empty($service->$getterName())) {
                        $attributes[$attribute->urns[0]] = $service->$getterName();
                    }
                    break;
                case $attribute->id === 'originalMetadataUrl':
                case $attribute->id === 'applicationUrl':
                case $attribute->id === 'eula':
                    $coin = $entity->getMetaData()?->getCoin();
                    if ($coin && $coin->$getterName()) {
                        $attributes[$attribute->urns[0]] = $coin->$getterName();
                    }
                    break;
                case $attribute->id === 'typeOfService':
                    $coin = $entity->getMetaData()?->getCoin();
                    // Does the Coin VO have types of service set? In that case add them to the
                    // attributes (in a manage multilingual fashion)
                    if ($coin && $coin->$getterName() && count($coin->getTypeOfService()->getArray()) > 0) {
                        // The nl and en attributes for the Type of service need to be set specifically
                        $attributes[$attribute->urns[0]] = $coin->getTypeOfService()->getServicesAsEnglishString();
                        $attributes[$attribute->urns[1]] = $coin->getTypeOfService()->getServicesAsDutchString();
                    }
                    break;
            }
        }
        return $attributes;
    }
}
