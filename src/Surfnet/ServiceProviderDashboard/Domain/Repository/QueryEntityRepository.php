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

namespace Surfnet\ServiceProviderDashboard\Domain\Repository;

interface QueryEntityRepository
{
    /**
     * @param string $entityId
     *
     * @return string
     */
    public function findManageIdByEntityId($entityId);

    /**
     * @param string $manageId
     *
     * @return array|null
     */
    public function findByManageId($manageId);

    /**
     * @param string $manageId
     *
     * @return string
     */
    public function getMetadataXmlByManageId($manageId);

    /**
     * @param string $teamName
     * @param string $state
     *
     * @return array|null
     */
    public function findByTeamName($teamName, $state);
}
