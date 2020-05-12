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

use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\QueryClient;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Exception\InvalidArgumentException;

class MangeQueryService
{
    private $validEnvironments = ['test', 'production'];

    /**
     * @var QueryClient
     */
    private $testQueryClient;

    /**
     * @var QueryClient
     */
    private $productionQueryClient;

    public function __construct(QueryClient $test, QueryClient $production)
    {
        $this->testQueryClient = $test;
        $this->productionQueryClient = $production;
    }

    public function findManageIdByEntityId($environment, $entityId)
    {
        return $this->getClient($environment)->findManageIdByEntityId($entityId);
    }

    public function findByManageId($environment, $manageId)
    {
        return $this->getClient($environment)->findByManageId($manageId);
    }

    public function getMetadataXmlByManageId($environment, $manageId)
    {
        return $this->getClient($environment)->getMetadataXmlByManageId($manageId);
    }

    public function findByTeamName($environment, $teamName, $state)
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
