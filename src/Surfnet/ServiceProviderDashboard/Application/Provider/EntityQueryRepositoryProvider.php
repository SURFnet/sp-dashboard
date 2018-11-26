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
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Repository\EntityRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\QueryClient as ManageQueryClient;

class EntityQueryRepositoryProvider
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


    public function __construct(
        EntityRepository $entityRepository,
        ManageQueryClient $manageTestQueryClient,
        ManageQueryClient $manageProductionQueryClient
    ) {
        $this->entityRepository = $entityRepository;
        $this->manageTestQueryClient = $manageTestQueryClient;
        $this->manageProductionQueryClient = $manageProductionQueryClient;
    }

    /**
     * @param string $environment
     * @return ManageQueryClient
     * @throws InvalidArgumentException
     */
    public function fromEnvironment($environment)
    {
        switch ($environment) {
            case Entity::ENVIRONMENT_TEST:
                return $this->manageTestQueryClient;
                break;
            case Entity::ENVIRONMENT_PRODUCTION:
                return $this->manageProductionQueryClient;
                break;
            default:
                throw new InvalidArgumentException(sprintf('Unsupported environment "%s" requested.', $environment));
                break;
        }
    }

    public function getEntityRepository()
    {
        return $this->entityRepository;
    }

    public function getManageTestQueryClient()
    {
        return $this->manageTestQueryClient;
    }

    public function getManageProductionQueryClient()
    {
        return $this->manageProductionQueryClient;
    }
}
