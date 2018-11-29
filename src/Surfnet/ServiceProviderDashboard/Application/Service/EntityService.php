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
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Application\Provider\EntityQueryRepositoryProvider;
use Surfnet\ServiceProviderDashboard\Application\ViewObject;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Symfony\Component\Routing\RouterInterface;

class EntityService implements EntityServiceInterface
{
    /**
     * @var EntityQueryRepositoryProvider
     */
    private $queryRepositoryProvider;

    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(
        EntityQueryRepositoryProvider $entityQueryRepositoryProvider,
        RouterInterface $router
    ) {
        $this->queryRepositoryProvider = $entityQueryRepositoryProvider;
        $this->router = $router;
    }

    public function createEntityUuid()
    {
        return (string)Uuid::uuid1();
    }

    public function getEntityById($id)
    {
        return $this->queryRepositoryProvider->getEntityRepository()->findById($id);
    }

    public function getEntityListForService(Service $service)
    {
        $entities = [];

        $draftEntities = $this->queryRepositoryProvider
            ->getEntityRepository()
            ->findByServiceId($service->getId());

        foreach ($draftEntities as $entity) {
            $entities[] = ViewObject\Entity::fromEntity($entity, $this->router);
        }

        $testEntities = $this->queryRepositoryProvider
            ->getManageTestQueryClient()
            ->findByTeamName($service->getTeamName());

        foreach ($testEntities as $result) {
            $entities[] = ViewObject\Entity::fromManageTestResult($result, $this->router);
        }

        $productionEntities = $this->queryRepositoryProvider
            ->getManageProductionQueryClient()
            ->findByTeamName($service->getTeamName());

        foreach ($productionEntities as $result) {
            $entities[] = ViewObject\Entity::fromManageProductionResult($result, $this->router);
        }

        return new ViewObject\EntityList($entities);
    }

    /**
     * @param string $manageId
     * @param string $env
     *
     * @return array|null
     *
     * @throws InvalidArgumentException
     * @throws \Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Exception\QueryServiceProviderException
     */
    public function getManageEntityById($manageId, $env = 'test')
    {
        return $this->queryRepositoryProvider
            ->fromEnvironment($env)
            ->findByManageId($manageId);
    }
}
