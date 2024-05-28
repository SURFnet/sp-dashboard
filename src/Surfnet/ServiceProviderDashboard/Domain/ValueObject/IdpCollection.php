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

use Surfnet\ServiceProviderDashboard\Domain\Entity\IdentityProvider;

class IdpCollection
{

    private array $testEntities;
    private array $institutionEntities;

    public function __construct(
        /** @var EntityId[] */
        array $testEntities,
        /** @var IdentityProvider[] */
        array $allEntities,
    ) {
        foreach ($allEntities as $idp) {
            if (array_key_exists($idp->getEntityId(), $testEntities)) {
                $this->testEntities[] = $idp;
                continue;
            }
            $this->institutionEntities[] = $idp;
        }
    }

    public function testEntities(): array
    {
        return $this->testEntities;
    }

    public function institutionEntities(): array
    {
        return $this->institutionEntities;
    }
}
