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
use Symfony\Component\Routing\RouterInterface;

class EntityService
{
    /**
     * @var EntityRepository
     */
    private $entityRepository;

    /**
     * @var ManageQueryClient
     */
    private $manageTestQueryClient;

    /**
     * @var ManageQueryClient
     */
    private $manageProductionQueryClient;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @param EntityRepository $entityRepository
     * @param ManageQueryClient $manageTestQueryClient
     * @param ManageQueryClient $manageProductionQueryClient
     * @param RouterInterface $router
     */
    public function __construct(
        EntityRepository $entityRepository,
        ManageQueryClient $manageTestQueryClient,
        ManageQueryClient $manageProductionQueryClient,
        RouterInterface $router
    ) {
        $this->entityRepository = $entityRepository;
        $this->manageTestQueryClient = $manageTestQueryClient;
        $this->manageProductionQueryClient = $manageProductionQueryClient;
        $this->router = $router;
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
            $entities[] = ViewObject\Entity::fromEntity($entity, $this->router);
        }

        foreach ($this->manageTestQueryClient->findByTeamName($service->getTeamName()) as $result) {
            $entities[] = ViewObject\Entity::fromManageTestResult($result, $this->router);
        }

        foreach ($this->manageProductionQueryClient->findByTeamName($service->getTeamName()) as $result) {
            $entities[] = ViewObject\Entity::fromManageProductionResult($result, $this->router);
        }

        return new ViewObject\EntityList($entities);
    }
}
