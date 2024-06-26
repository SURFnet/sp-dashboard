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

namespace Surfnet\ServiceProviderDashboard\Application\Service;

use Surfnet\ServiceProviderDashboard\Application\Dto\EntityDto;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Application\ViewObject\EntityList;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\QueryServiceProviderException;

interface EntityServiceInterface
{
    /**
     * @return string
     */
    public function createEntityUuid();

    public function getEntityByIdAndTarget(string $id, string $manageTarget, Service $service): ManageEntity;

    /**
     * @return EntityList
     */
    public function getEntityListForService(Service $service);

    /**
     * @return EntityDto[]
     */
    public function getEntitiesForService(Service $service);

    /**
     * @param  string $manageId
     * @param  string $env
     * @return ManageEntity|null
     *
     * @throws InvalidArgumentException
     * @throws \Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\QueryServiceProviderException
     */
    public function getManageEntityById($manageId, $env = 'test');

    /**
     * @param  string $manageId
     * @param  string $env
     * @return ManageEntity|null
     *
     * @throws InvalidArgumentException
     * @throws \Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\QueryServiceProviderException
     */
    public function getPristineManageEntityById($manageId, $env = 'test');

    /**
     * @param  string $teamName
     * @return ManageEntity[]|null
     * @throws QueryServiceProviderException
     */
    public function findPublishedTestEntitiesByTeamName(string $teamName): ?array;
}
