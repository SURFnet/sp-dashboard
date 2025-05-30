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

namespace Surfnet\ServiceProviderDashboard\Domain\Repository;

use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Exception\TypeOfServiceException;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\TypeOfService;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\TypeOfServiceCollection;

class TypeOfServiceRepositoryFromConfig implements TypeOfServiceRepository
{
    private TypeOfServiceCollection $collection;
    private string $typeOfServiceLocation = Constants::TYPE_OF_SERVICE_LOCATION;

    public function __construct(?string $typeOfServiceLocation = null)
    {
        // Allow overwriting the default typeOfService location. This is particularly useful for testing
        if ($typeOfServiceLocation !== null) {
            $this->typeOfServiceLocation = $typeOfServiceLocation;
        }
        $this->load();
    }

    private function load(): void
    {
        if (!file_exists($this->typeOfServiceLocation)) {
            throw new TypeOfServiceException(
                sprintf(
                    'Please review the file location of the type of services json blob. %s',
                    $this->typeOfServiceLocation
                )
            );
        }
        $fileContents = file_get_contents($this->typeOfServiceLocation);
        if (!$fileContents) {
            throw new TypeOfServiceException('Unable to load the type of service json file.');
        }
        $data = json_decode($fileContents);
        if (!is_array($data)) {
            throw new TypeOfServiceException('The json can not be parsed into an array of service types');
        }
        $this->collection = new TypeOfServiceCollection();
        foreach ($data as $entry) {
            $typeHidden = $entry->typeHidden ?? false;
            $typeOfService = new TypeOfService($entry->typeEn, $entry->typeNl, $typeHidden);
            $this->collection->add($typeOfService);
        }
    }

    /**
     * @return array<TypeOfService>
     */
    public function getTypesOfServiceChoices(): array
    {
        return $this->collection->filterHidden()->getArray();
    }

    public function findByEnglishTypeOfService(string $enTos): ?TypeOfService
    {
        if ($this->collection->has($enTos)) {
            return $this->collection->get($enTos);
        }
        return null;
    }
}
