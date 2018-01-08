<?php

/**
 * Copyright 2017 SURFnet B.V.
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
namespace Surfnet\ServiceProviderDashboard\Application\Service;

use Ramsey\Uuid\Uuid;
use Surfnet\ServiceProviderDashboard\Application\ViewObject;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Repository\EntityRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\QueryClient as ManageQueryClient;

class EntityService
{
    /**
     * @var EntityRepository
     */
    private $entityRepository;

    /**
     * @var ManageQueryClient
     */
    private $manageQueryClient;

    /**
     * @param EntityRepository $entityRepository
     * @param ManageQueryClient $manageQueryClient
     */
    public function __construct(
        EntityRepository $entityRepository,
        ManageQueryClient $manageQueryClient
    ) {
        $this->entityRepository = $entityRepository;
        $this->manageQueryClient = $manageQueryClient;
    }

    /**
     * @return string
     */
    public function createEntityUuid()
    {
        return (string) Uuid::uuid1();
    }

    /**
     * @param $id
     *
     * @return Entity|null
     */
    public function getEntityById($id)
    {
        return $this->entityRepository->findById($id);
    }

    /**
     * @param Service $service
     *
     * @return ViewObject\EntityList
     */
    public function getEntityListForService(Service $service)
    {
        $entities = [];

        foreach ($this->entityRepository->findByServiceId($service->getId()) as $entity) {
            $entities[] = ViewObject\Entity::fromEntity($entity);
        }

        foreach ($this->manageQueryClient->findByTeamName($service->getTeamName()) as $result) {
            $entities[] = ViewObject\Entity::fromManageResult($result);
        }

        return new ViewObject\EntityList($entities);
    }
}
