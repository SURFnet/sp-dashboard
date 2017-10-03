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

use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Repository\ServiceRepository as SupplierRepositoryInterface;
use Surfnet\ServiceProviderDashboard\Domain\Repository\ServiceRepository;

class InMemoryServiceRepository implements ServiceRepository
{
    private static $memory = [];

    public function clear()
    {
        self:$memory = [];
    }

    /**
     * @return Supplier[]
     */
    public function findAll()
    {
        return self::$memory;
    }

    /**
     * @param Service $service
     */
    public function save(Service $service)
    {
        self::$memory[] = $service;
    }

    /**
     * @param int $id
     *
     * @return bool
     */
    public function isUnique($id)
    {
        $allServices = $this->findAll();
        foreach ($allServices as $service) {
            if ($service->getId() == $id) {
                return false;
            }
        }

        return true;
    }
}
