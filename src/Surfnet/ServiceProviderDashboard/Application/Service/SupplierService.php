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
namespace Surfnet\ServiceProviderDashboard\Application\Service;

use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Repository\SupplierRepository;

class SupplierService
{
    /**
     * @var SupplierRepository
     */
    private $suppliers;

    /**
     * @param SupplierRepository $suppliers
     */
    public function __construct(SupplierRepository $suppliers)
    {
        $this->suppliers = $suppliers;
    }

    /**
     * Retrieve names of all suppliers.
     *
     * Format: [ '<supplier id>' => '<supplier display name>' ]
     * @return array
     */
    public function getSupplierNames()
    {
        $options = [];

        foreach ($this->suppliers->findAll() as $supplier) {
            $options[$supplier->getId()] = $supplier->getName();
        }

        asort($options);

        return $options;
    }

    /**
     * @param int $id
     *
     * @return Supplier|null
     */
    public function getSupplierById($id)
    {
        return $this->suppliers->findById($id);
    }
}
