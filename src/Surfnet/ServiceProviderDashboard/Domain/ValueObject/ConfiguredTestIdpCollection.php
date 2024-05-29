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

use Surfnet\ServiceProviderDashboard\Domain\Exception\InvalidEntityIdException;

class ConfiguredTestIdpCollection
{
    /** @var EntityId[] */
    private array $entityIds;

    /** @param string[]|null $entityIds */
    public function __construct(?array $entityIds)
    {
        $this->entityIds = [];
        if ($entityIds === null) {
            throw new InvalidEntityIdException(
                'Please review the configured test IdP config entries. No Idps were provided.'
            );
        }
        foreach ($entityIds as $entityId) {
            if (!is_string($entityId) || $entityId === '') {
                throw new InvalidEntityIdException(
                    'Please review the configured test IdP config entries. Only non empty strings are allowed'
                );
            }
            $this->entityIds[$entityId] = new EntityId($entityId);
        }
    }
    /**
     * @return EntityId[]
     */
    public function testEntities(): array
    {
        return $this->entityIds;
    }
}
