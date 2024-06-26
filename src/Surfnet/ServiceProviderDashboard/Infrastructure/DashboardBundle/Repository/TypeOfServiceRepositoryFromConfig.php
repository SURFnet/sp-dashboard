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

use Surfnet\ServiceProviderDashboard\Application\Exception\RuntimeException;
use Surfnet\ServiceProviderDashboard\Domain\Repository\TypeOfServiceRepository;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\TypeOfService;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\TypeOfServiceCollection;
use function file_exists;
use function is_array;

class TypeOfServiceRepositoryFromConfig implements TypeOfServiceRepository
{
    private TypeOfServiceCollection $collection;

    public function __construct(
        private readonly string $typeOfServiceLocation,
    ) {
    }

    private function load(): void
    {
        if (!file_exists($this->typeOfServiceLocation)) {
            throw new RuntimeException(
                sprintf(
                    'Please review the file location of the type of services json blob. %s',
                    $this->typeOfServiceLocation
                )
            );
        }
        $fileContents = file_get_contents($this->typeOfServiceLocation);
        if (!$fileContents) {
            throw new RuntimeException('Unable to load the type of service json file.');
        }
        $data = json_decode($fileContents);
        if (!is_array($data)) {
            throw new RuntimeException('The json can not be parsed into an array of service types');
        }
        $this->collection = new TypeOfServiceCollection();
        foreach ($data as $entry) {
            $typeOfService = new TypeOfService($entry->typeNl, $entry->typeEn);
            $this->collection->add($typeOfService);
        }
    }

    /**
     * @return array<TypeOfService>
     */
    public function getTypesOfServiceChoices(): array
    {
        $this->load();
        return $this->collection->getArray();
    }
}
