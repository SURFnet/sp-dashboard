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

use RuntimeException;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\Repository\QueryEntityRepository;
use function array_key_exists;
use function json_decode;

class FakeQueryClient implements QueryEntityRepository
{
    /**
     * @var ClientResult[]
     */
    private $entities = [];

    public function registerEntity(string $protocol, string $id, string $entityId, ?string $metadataUrl, string $name, ?string $teamName = null)
    {
        $this->entities[$id] = new ClientResult($protocol, $id, $entityId, $metadataUrl, $name, $teamName);
    }
    public function registerEntityRaw(string $json)
    {
        // Yank the id from the json metadata
        $decoded = json_decode($json, true);
        $this->entities[$decoded['id']] = new ClientResultRaw($json);
    }

    public function findManageIdByEntityId($entityId)
    {
        foreach ($this->entities as $entity) {
            if ($entity->getEntityId() === $entityId) {
                return $entity->getId();
            }
        }
    }

    public function getMetadataXmlByManageId($manageId)
    {
        throw new RuntimeException('This method "getMetadataXmlByManageId" is not yet supported');
    }

    public function findByManageId($manageId)
    {
        if (array_key_exists($manageId, $this->entities)) {
            return ManageEntity::fromApiResponse($this->entities[$manageId]->getEntityResult());
        }
        return null;
    }

    public function findByTeamName($teamName, $state)
    {
        $searchResults = [];
        foreach ($this->entities as $entity) {
            $result = $entity->getEntityResult();
            if (isset($result['data']['metaDataFields']['coin:service_team_id']) && $result['data']['metaDataFields']['coin:service_team_id'] === $teamName) {
                $searchResults[] = ManageEntity::fromApiResponse($result);
            }
        }
        return $searchResults;
    }

    public function findOidcngResourceServersByTeamName(string $teamName, string $state): array
    {
        $results = [];
        $entities = $this->findByTeamName($teamName, $state);
        foreach ($entities as $entity) {
            if ($entity->isOidcngResourceServer()) {
                $results[] = $entity;
            }
        }
        return $results;
    }

    public function findByEntityId($entityId, $state)
    {
        foreach ($this->entities as $entity) {
            $result = $entity->getEntityResult();
            if (isset($result['data']['entityid']) && $result['data']['entityid'] === $entityId) {
                $searchResults[] = ManageEntity::fromApiResponse($result);
            }
        }
    }
}
