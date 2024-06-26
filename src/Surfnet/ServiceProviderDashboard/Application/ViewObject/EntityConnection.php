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

class EntityConnection
{
    public function __construct(
        public string $entityName,
        public string $entityId,
        public string $vendorName,
        /** @var array<string, IdentityProvider> $availableTestIdps */
        private array $availableTestIdps,
        /** @var array<string, IdentityProvider> $availableOtherIdps */
        private array $availableOtherIdps,
        /** @var array<string, IdentityProvider> $connectedIdps */
        private array $connectedIdps,
    ) {
    }

    /**
     * @return array<string, bool>
     */
    public function listConnected(): array
    {
        $list = [];
        foreach (array_keys($this->availableTestIdps) as $entityId) {
            $list[$entityId] = false;
            if (array_key_exists($entityId, $this->connectedIdps)) {
                $list[$entityId] = true;
            }
        }
        return $list;
    }

    public function hasConnectedOtherIdp(): bool
    {
        $intersection = array_intersect(array_keys($this->availableOtherIdps), array_keys($this->connectedIdps));
        if (count($intersection) > 0) {
            return true;
        }
        return false;
    }

    /**
     * Returns the test entities that are available to the services
     * These are the .env configurable test_idp_entity_ids
     * @return array<string, IdentityProvider>
     */
    public function listAvailableTestIdps(): array
    {
        return $this->availableTestIdps;
    }
}
