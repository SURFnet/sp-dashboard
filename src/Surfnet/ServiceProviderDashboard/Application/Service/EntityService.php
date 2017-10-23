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
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\EditEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Factory\EntityCommandFactory;
use Surfnet\ServiceProviderDashboard\Application\ViewObject;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Repository\EntityRepository;

class EntityService
{
    /**
     * @var EntityRepository
     */
    private $entityRepository;

    /**
     * @var EntityCommandFactory
     */
    private $factory;

    /**
     * @param EntityRepository $entityRepository
     * @param EntityCommandFactory $factory
     */
    public function __construct(
        EntityRepository $entityRepository,
        EntityCommandFactory $factory
    ) {
        $this->entityRepository = $entityRepository;
        $this->factory = $factory;
    }

    /**
     * @return string
     */
    public function createEntityUuid()
    {
        return (string) Uuid::uuid1();
    }

    /**
     * @param $entityId
     *
     * @return Entity|null
     */
    public function getEntityById($entityId)
    {
        return $this->entityRepository->findById($entityId);
    }

    /**
     * @param Entity $entity
     *
     * @return EditEntityCommand
     */
    public function buildEditEntityCommand(Entity $entity)
    {
        return $this->factory->build($entity);
    }

    /**
     * @param int $serviceId
     *
     * @return ViewObject\EntityList
     */
    public function getEntityListForService($serviceId)
    {
        $entities = [];

        foreach ($this->entityRepository->findByServiceId($serviceId) as $entity) {
            $entities[] = ViewObject\Entity::fromEntity($entity);
        }

        return new ViewObject\EntityList($entities);
    }
}
