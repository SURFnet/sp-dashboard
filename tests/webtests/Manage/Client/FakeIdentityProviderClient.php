<?php

/**
 * Copyright 2020 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Webtests\Manage\Client;

use Surfnet\ServiceProviderDashboard\Domain\Entity\IdentityProvider;
use Surfnet\ServiceProviderDashboard\Domain\Repository\IdentityProviderRepository;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\EntityId;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\InstitutionId;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Factory\IdentityProviderFactory;

class FakeIdentityProviderClient implements IdentityProviderRepository
{
    /**
     * @var ClientResult[]
     */
    private $entities = [];

    public function registerEntity(string $protocol, string $id, string $entityId, string $name)
    {
        $this->entities[$id] = new ClientResult($protocol, $id, $entityId, null, $name, null);
    }

    /**
     * @return IdentityProvider[]
     */
    public function findAll()
    {
        $list = [];
        foreach ($this->entities as $manageResult) {
            $list[] = IdentityProviderFactory::fromManageResult($manageResult->getEntityResult());
        }
        return $list;
    }

    public function findByEntityId(EntityId $entityId): ?IdentityProvider
    {
        foreach ($this->entities as $manageResult) {
            $entity = IdentityProviderFactory::fromManageResult($manageResult->getEntityResult());
            if ($entity->getEntityId() === (string) $entityId) {
                return $entity;
            }
        }
        return null;
    }

    /**
     * In this fake implementation, all IdP's are considered
     * institutional IdPs
     * @return IdentityProvider[]
     */
    public function findByInstitutionId(InstitutionId $institutionId): array
    {
        $list = [];
        foreach ($this->entities as $manageResult) {
            $list[] = IdentityProviderFactory::fromManageResult($manageResult->getEntityResult());
        }
        return $list;
    }
}
