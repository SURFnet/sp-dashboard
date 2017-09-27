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

namespace Surfnet\ServiceProviderDashboard\Application\CommandHandler\Supplier;

use Surfnet\ServiceProviderDashboard\Application\Command\Supplier\CreateSupplier;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\CommandHandler;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Supplier;
use Surfnet\ServiceProviderDashboard\Domain\Repository\SupplierRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateSupplierCommandHandler implements CommandHandler
{
    /**
     * @var SupplierRepository
     */
    private $supplierRepository;

    /**
     * @param SupplierRepository $supplierRepository
     */
    public function __construct(SupplierRepository $supplierRepository)
    {
        $this->supplierRepository = $supplierRepository;
    }

    /**
     * @param CreateSupplier $command
     * @throws InvalidArgumentException
     */
    public function handle(CreateSupplier $command)
    {
        $supplier = new Supplier();
        $supplier->setName($command->getName());
        $supplier->setGuid($command->getGuid());
        $supplier->setTeamName($command->getTeamName());

        if (!$this->supplierRepository->isUnique($supplier)) {
            throw new InvalidArgumentException('Non unique field values in CreateSupplier command');
        }

        $this->supplierRepository->save($supplier);
    }
}
