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

use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;

interface QueryManageRepository
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
     * @return ManageEntity|null
     */
    public function findByManageId($manageId);

    /**
     * Use of this method is discouraged, it will try saml, openic and oauth endpoints to find
     * the entity. If you already know the data type (protocol) of the entity,
     * please use the findByManageIdAndProtocol instead
     */
    public function findByManageIdAndProtocol(string $manageId, string $protocol) :? ManageEntity;

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
     * @return ManageEntity[]|null
     */
    public function findByTeamName($teamName, $state);

    /**
     * The entity Id or client Id for RP's should also be unique, and can be used to search a SP/RP with.
     *
     * @param string $entityId
     * @param string $state
     *
     * @return ManageEntity|null
     */
    public function findResourceServerByEntityId($entityId, $state);

    public function findOidcngResourceServersByTeamName(string $teamName, string $state): array;
}
