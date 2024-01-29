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

use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;

interface ServiceRepository
{
    public function save(Service $service);

    /**
     * Is the proposed service entity unique? The id of the service is not taken into account in this test.
     *
     * @return bool
     */
    public function isUnique(Service $service);

    /**
     * @return Service[]
     */
    public function findAll();

    /**
     * @param int $id
     *
     * @return Service|null
     */
    public function findById($id);

    /**
     * Find a service by name.
     *
     * @param string $name
     *
     * @return Service|null
     */
    public function findByName($name);

    /**
     * Find all services for given team names.
     *
     * @param string[] $teamNames
     *
     * @return Service[]
     */
    public function findByTeamNames($teamNames);

    /**
     * Delete a service
     *
     * @return mixed
     */
    public function delete(Service $service);

    public function findByTeamName(?string $serviceTeamName): ?Service;
}
