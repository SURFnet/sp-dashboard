<?php

declare(strict_types = 1);

/**
 * Copyright 2024 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Domain\ValueObject;

use Surfnet\ServiceProviderDashboard\Domain\Exception\TypeOfServiceException;
use Surfnet\ServiceProviderDashboard\Domain\Repository\TypeOfServiceRepositoryFromConfig;
use function implode;

class TypeOfServiceCollection
{
    /** @var array<TypeOfService> */
    private array $types = [];

    public static function createFromManageResponse(array $metaDataFields): TypeOfServiceCollection
    {
        $collection = new TypeOfServiceCollection();
        $repository = new TypeOfServiceRepositoryFromConfig();
        $englishEntry = $metaDataFields['coin:ss:type_of_service:en'] ?? '';
        if ($englishEntry === '') {
            return $collection;
        }
        $englishEnties = explode(',', $englishEntry);
        foreach ($englishEnties as $singleEntity) {
            // When loading the entities from manage, we only load the english types of service.
            // The Dutch translations are only relevant when writing to Manage
            $searchResult = $repository->findByEnglishTypeOfService($singleEntity);
            if ($searchResult !== null) {
                $collection->add($searchResult);
            }
        }
        return $collection;
    }

    public function add(TypeOfService $type): void
    {
        $this->types[] = $type;
    }

    /**
     * @return array<TypeOfService>
     */
    public function getArray(): array
    {
        return $this->types;
    }

    public function has(string $englishTypeOfService): bool
    {
        foreach ($this->types as $type) {
            if ($type->typeEn === $englishTypeOfService) {
                return true;
            }
        }
        return false;
    }

    public function get(string $englishTypeOfService): TypeOfService
    {
        foreach ($this->types as $type) {
            if ($type->typeEn === $englishTypeOfService) {
                return $type;
            }
        }
        throw new TypeOfServiceException(
            sprintf(
                'Type of Service with English name %s could not be located',
                $englishTypeOfService
            )
        );
    }

    public function getServicesAsDutchString(): string
    {
        $commasSeperated = [];
        foreach ($this->types as $type) {
            $commasSeperated[] = $type->typeNl;
        }
        return implode(',', $commasSeperated);
    }

    public function getServicesAsEnglishString(): string
    {
        $commasSeperated = [];
        foreach ($this->types as $type) {
            $commasSeperated[] = $type->typeEn;
        }
        return implode(',', $commasSeperated);
    }
}
