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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Supplier;
use Surfnet\ServiceProviderDashboard\Domain\Repository\SupplierRepository as SupplierRepositoryInterface;

class SupplierRepository extends EntityRepository implements SupplierRepositoryInterface
{
    /**
     * @param Supplier $supplier
     */
    public function save(Supplier $supplier)
    {
        $this->_em->persist($supplier);
        $this->_em->flush($supplier);
    }

    /**
     * @param Supplier $supplier
     * @return bool
     */
    public function isUnique(Supplier $supplier)
    {
        $this->isGuidUnique($supplier->getGuid());
        $this->isTeamNameUnique($supplier->getTeamName());
        return true;
    }

    /**
     * @param string $guid
     * @throws InvalidArgumentException
     */
    private function isGuidUnique($guid)
    {
        $supplierExists = $this->createQueryBuilder('s')
            ->where('s.guid = :guid')
            ->setParameter('guid', $guid)
            ->getQuery()
            ->getOneOrNullResult();

        if ($supplierExists) {
            throw new InvalidArgumentException(
                sprintf(
                    'The Guid of the new Supplier should be unique. This teamname is taken by: "%s" with Guid: "%s"',
                    $supplierExists->getName(),
                    $supplierExists->getGuid()
                )
            );
        }
    }

    /**
     * @param string {
     * @throws InvalidArgumentException
     */
    private function isTeamNameUnique($teamName)
    {
        $supplierExists = $this->createQueryBuilder('s')
            ->where('s.teamName = :teamname')
            ->setParameter('teamname', $teamName)
            ->getQuery()
            ->getOneOrNullResult();

        if ($supplierExists) {
            throw new InvalidArgumentException(
                sprintf(
                    'The teamname of the new Supplier should be unique. This teamname is taken by: "%s" with Guid: "%s"',
                    $supplierExists->getName(),
                    $supplierExists->getGuid()
                )
            );
        }
    }

    /**
     * @param int $id
     * @return Supplier|null
     */
    public function findById($id)
    {
        return $this->find($id);
    }
}
