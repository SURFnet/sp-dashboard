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

namespace Surfnet\ServiceProviderDashboard\Webtests\Repository;

use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Supplier;
use Surfnet\ServiceProviderDashboard\Domain\Repository\SupplierRepository as SupplierRepositoryInterface;

class InMemorySupplierRepository implements SupplierRepositoryInterface
{
    /**
     * @var Supplier[]
     */
    private static $memory = [];

    public function clear()
    {
        self::$memory = [];
    }

    /**
     * @param Supplier $supplier
     */
    public function save(Supplier $supplier)
    {
        self::$memory[] = $supplier;
    }

    /**
     * @param Supplier $supplier
     * @return bool
     * @throws InvalidArgumentException
     */
    public function isUnique(Supplier $supplier)
    {
        foreach (self::$memory as $storedSupplier) {
            if ($storedSupplier->getGuid() == $supplier->getGuid()) {
                throw new InvalidArgumentException(
                    sprintf(
                        'The Guid of the Supplier should be unique. This Guid is taken by: "%s"',
                        $storedSupplier->getName()
                    )
                );
            }
        }
        return true;
    }

    /**
     * @return Supplier[]
     */
    public function findAll()
    {
        return self::$memory;
    }

    /**
     * @param int $id
     *
     * @return Supplier|null
     */
    public function findById($id)
    {
        $allSuppliers = $this->findAll();
        foreach ($allSuppliers as $supplier) {
            if ($supplier->getId() == $id) {
                return $supplier;
            }
        }

        return null;
    }

    /**
     * @param string[] $teamNames
     *
     * @return Supplier[]
     */
    public function findByTeamNames($teamNames)
    {
        $result = [];

        foreach ($this->findAll() as $supplier) {
            if (in_array($supplier->getTeamName(), $teamNames)) {
                $result[] = $supplier;
            }
        }

        return $result;
    }
}
