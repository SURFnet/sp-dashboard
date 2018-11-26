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
use Surfnet\ServiceProviderDashboard\Application\ViewObject;
use Surfnet\ServiceProviderDashboard\Application\ViewObject\EntityList;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Repository\EntityRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\QueryClient as ManageQueryClient;
use Symfony\Component\Routing\RouterInterface;

class EntityService implements EntityServiceInterface
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

    public function createEntityUuid()
    {
        return (string) Uuid::uuid1();
    }

    public function getEntityById($id)
    {
        return $this->entityRepository->findById($id);
    }

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
        switch ($env) {
            case "test":
                $manageClient = $this->manageTestQueryClient;
                break;
            case "production":
                $manageClient = $this->manageProductionQueryClient;
                break;
            default:
                throw new InvalidArgumentException(sprintf('Unsupported Manage environment "%s" requested.', $env));
                break;
        }
        return $manageClient->findByManageId($manageId);
    }

    public function removeFrom(EntityList $list)
    {
    }
}
