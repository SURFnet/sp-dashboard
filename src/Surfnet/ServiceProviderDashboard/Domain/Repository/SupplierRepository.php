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

use Surfnet\ServiceProviderDashboard\Domain\Entity\Supplier;

interface SupplierRepository
{
    /**
     * @param Supplier $supplier
     */
    public function save(Supplier $supplier);

    /**
     * Is the proposed supplier entity unique? The id of the supplier is not taken into account in this test.
     *
     * @param Supplier $supplier
     *
     * @return bool
     */
    public function isUnique(Supplier $supplier);

    /**
     * @return Supplier[]
     */
    public function findAll();

    /**
     * @param int $id
     *
     * @return Supplier|null
     */
    public function findById($id);

    /**
     * Find all suppliers for given team names.
     *
     * @param string[] $teamNames
     *
     * @return Supplier[]
     */
    public function findByTeamNames($teamNames);
}
