<?php

/**
 * Copyright 2018 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Application\Provider;

use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Repository\EntityRepository;
use Surfnet\ServiceProviderDashboard\Domain\Repository\QueryEntityRepository;

class EntityQueryRepositoryProvider
{
    /**
     * @var EntityRepository
     */
    private $entityRepository;

    /**
     * @var QueryEntityRepository
     */
    private $manageTestQueryClient;

    /**
     * @var QueryEntityRepository
     */
    private $manageProductionQueryClient;


    public function __construct(
        EntityRepository $entityRepository,
        QueryEntityRepository $manageTestQueryClient,
        QueryEntityRepository $manageProductionQueryClient
    ) {
        $this->entityRepository = $entityRepository;
        $this->manageTestQueryClient = $manageTestQueryClient;
        $this->manageProductionQueryClient = $manageProductionQueryClient;
    }

    public function fromEnvironment(string $environment): QueryEntityRepository
    {
        switch ($environment) {
            case Constants::ENVIRONMENT_TEST:
                return $this->manageTestQueryClient;
            case Constants::ENVIRONMENT_PRODUCTION:
                return $this->manageProductionQueryClient;
            default:
                throw new InvalidArgumentException(sprintf('Unsupported environment "%s" requested.', $environment));
        }
    }

    public function getEntityRepository(): EntityRepository
    {
        return $this->entityRepository;
    }

    public function getManageTestQueryClient(): QueryEntityRepository
    {
        return $this->manageTestQueryClient;
    }

    public function getManageProductionQueryClient(): QueryEntityRepository
    {
        return $this->manageProductionQueryClient;
    }
}
