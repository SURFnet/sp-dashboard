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

namespace Surfnet\ServiceProviderDashboard\Application\ViewObject;

use Surfnet\ServiceProviderDashboard\Domain\Entity\IdentityProvider;

class EntityConnectionCollection
{
    /**
     * @var array<string, IdentityProvider> $testIdpList
     */
    private array $testIdpList = [];

    /** @var array<string, array<int, EntityConnection>> $collectionByServiceName */
    private array $collectionByServiceName = [];

    public static function empty(): self
    {
        return new self();
    }

    public function addIdpList(array $idps): void
    {
        $this->testIdpList = $this->testIdpList + $idps;
    }

    /**
     * @return array<string, IdentityProvider>
     */
    public function getTestIdps(): array
    {
        return $this->testIdpList;
    }

    /**
     * @return string[]
     */
    public function services(): array
    {
        return array_keys($this->collectionByServiceName);
    }

    /**
     * @return array<EntityConnection>
     */
    public function entityConnectionsByServiceName(string $serviceName): array
    {
        if (array_key_exists($serviceName, $this->collectionByServiceName)) {
            return $this->collectionByServiceName[$serviceName];
        }
        return [];
    }

    /**
     * @param array<EntityConnection> $entityConnections
     */
    public function addEntityConnections(array $entityConnections): void
    {
        foreach ($entityConnections as $connection) {
            $this->collectionByServiceName[$connection->vendorName][] = $connection;
        }
    }

    public function export()
    {
        $list = [];
        foreach ($this->collectionByServiceName as $serviceEntities) {
            foreach ($serviceEntities as $entity) {
                $list[] = $entity;
            }
        }
        return $list;
    }
}
