<?php

declare(strict_types = 1);

/**
 * Copyright 2022 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Repository;

use Surfnet\ServiceProviderDashboard\Domain\Repository\TypeOfServiceRepository;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\TypeOfService;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\TypeOfServiceCollection;

class TypeOfServiceRepositoryFromConfig implements TypeOfServiceRepository
{
    private TypeOfServiceCollection $collection;

    public function __construct(
        private readonly string $typeOfServiceLocation,
    ) {
    }

    private function load(): void
    {
        $data = json_decode(file_get_contents($this->typeOfServiceLocation));
        $this->collection = new TypeOfServiceCollection();
        foreach ($data as $entry) {
            $typeOfService = new TypeOfService($entry->id, $entry->typeNl, $entry->typeEn);
            $this->collection->add($typeOfService);
        }
    }

    /**
     * @return array<string, string>
     */
    public function getTypesOfServiceChoices(): array
    {
        $this->load();
        $typesOfService = $this->collection->getArray();
        $choices = [];
        foreach ($typesOfService as $typeOfService) {
            $choices[$typeOfService->typeEn] = $typeOfService->typeIdentifier;
        }
        return $choices;
    }
}
