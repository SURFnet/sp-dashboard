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
    private string $path = __DIR__ . '/../../../../var/webtest-idps.json';
    /**
     * @var ClientResult[]
     */
    private $entities = [];

    public function registerEntity(string $protocol, string $id, string $entityId, string $name, string $institutionId = '')
    {
        $this->entities[$id] = new ClientResult($protocol, $id, $entityId, null, $name, null, $institutionId, null);
        $this->storeEntities();
    }

    /**
     * @return IdentityProvider[]
     */
    public function findAll()
    {
        $this->load();
        $list = [];
        foreach ($this->entities as $manageResult) {
            $idp = IdentityProviderFactory::fromManageResult($manageResult->getEntityResult());
            $list[$idp->getEntityId()] = $idp;
        }
        return $list;
    }

    public function findByEntityId(EntityId $entityId): ?IdentityProvider
    {
        $this->load();
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
        $this->load();
        $list = [];
        foreach ($this->entities as $manageResult) {
            $list[] = IdentityProviderFactory::fromManageResult($manageResult->getEntityResult());
        }
        return $list;
    }


    private function read()
    {
        return json_decode(file_get_contents($this->path), true);
    }

    private function write(array $data)
    {
        file_put_contents($this->path, json_encode($data));
    }

    private function storeEntities()
    {
        // Also store the new entity in the on-file storage
        $data = [];
        foreach ($this->entities as $identifier => $entity) {
            $data[$identifier] = $entity->encode();
        }
        $this->write($data);
    }

    private function load()
    {
        $data = $this->read();
        foreach ($data as $id => $rawClientResult) {
            if (array_key_exists('protocol', $rawClientResult)) {
                $this->entities[$id] = ClientResult::decode($rawClientResult);
                continue;
            }
            if (array_key_exists('json', $rawClientResult)) {
                $this->entities[$id] = ClientResultRaw::decode($rawClientResult);
            }
        }
    }
}
