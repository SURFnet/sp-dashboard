<?php

declare(strict_types = 1);

/**
 * Copyright 2025 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Domain\Service;

use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\TypeOfServiceCollection;

class TypeOfServiceService
{
    public function restoreHiddenTypes(ManageEntity $entity, ?ManageEntity $pristineEntity): TypeOfServiceCollection
    {
        $typeOfService = new TypeOfServiceCollection();
        if ($entity->getMetaData()?->getCoin()->hasTypeOfService() === true) {
            $typeOfService = $entity->getMetaData()->getCoin()->getTypeOfService();
        }

        if ($pristineEntity && $pristineEntity->getMetaData()?->getCoin()->hasTypeOfService()) {
            $pristineTypeOfService = $pristineEntity->getMetaData()->getCoin()->getTypeOfService();
        }

        return $this->mergeCollections($typeOfService, $pristineTypeOfService ?? null);
    }

    public function mergeCollections(
        TypeOfServiceCollection $types,
        ?TypeOfServiceCollection $pristineTypes
    ): TypeOfServiceCollection {
        $result = new TypeOfServiceCollection();

        $this->addNonHiddenTypes($types, $result);

        if ($pristineTypes === null) {
            return $result;
        }

        $this->restoreHiddenPristineTypes($pristineTypes, $result);

        return $result;
    }

    private function addNonHiddenTypes(TypeOfServiceCollection $types, TypeOfServiceCollection $result): void
    {
        foreach ($types->getArray() as $type) {
            if (!$type->typeHidden && !$result->has($type->typeEn)) {
                $result->add($type);
            }
        }
    }

    private function restoreHiddenPristineTypes(TypeOfServiceCollection $pristineTypes, TypeOfServiceCollection $result): void
    {
        foreach ($pristineTypes->getArray() as $pristineType) {
            if ($pristineType->typeHidden && !$result->has($pristineType->typeEn)) {
                $result->add($pristineType);
            }
        }
    }
}
