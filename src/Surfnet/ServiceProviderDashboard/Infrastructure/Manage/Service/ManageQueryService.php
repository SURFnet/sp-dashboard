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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Service;

use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\Repository\QueryManageRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\QueryServiceProviderException;

class ManageQueryService
{
    private $validEnvironments = ['test', 'production'];

    /**
     * @var QueryManageRepository
     */
    private $testQueryClient;

    /**
     * @var QueryManageRepository
     */
    private $productionQueryClient;

    public function __construct(QueryManageRepository $test, QueryManageRepository $production)
    {
        $this->testQueryClient = $test;
        $this->productionQueryClient = $production;
    }

    public function findManageIdByEntityId(string $environment, ?string $entityId): ?string
    {
        return $this->getClient($environment)->findManageIdByEntityId($entityId);
    }

    public function findByManageId(string $environment, string $manageId): ?ManageEntity
    {
        return $this->getClient($environment)->findByManageId($manageId);
    }

    public function getMetadataXmlByManageId(string $environment, string $manageId): string
    {
        return $this->getClient($environment)->getMetadataXmlByManageId($manageId);
    }

    /**
     * @return ManageEntity[]|null
     * @throws InvalidArgumentException
     * @throws QueryServiceProviderException
     */
    public function findByTeamName(string $environment, string $teamName, string $state): ?array
    {
        return $this->getClient($environment)->findByTeamName($teamName, $state);
    }

    private function getClient($environment)
    {
        if (!in_array($environment, $this->validEnvironments)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Please set the query mode to one of these environments (%s)',
                    implode(', ', $this->validEnvironments)
                )
            );
        }
        if ($environment === 'test') {
            return $this->testQueryClient;
        }
        return $this->productionQueryClient;
    }
}
