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
        $this->getEntityManager()->persist($supplier);
        $this->getEntityManager()->flush($supplier);
    }

    /**
     * @param Supplier $supplier
     * @return bool
     */
    public function isUnique(Supplier $supplier)
    {
        $this->isGuidUnique($supplier->getGuid(), $supplier->getId());
        $this->isTeamNameUnique($supplier->getTeamName(), $supplier->getId());
        return true;
    }

    /**
     * @param string $guid
     * @param null|int $id
     * @throws InvalidArgumentException
     */
    private function isGuidUnique($guid, $id = null)
    {
        $qb = $this->createQueryBuilder('s')
            ->where('s.guid = :guid')
            ->setParameter('guid', $guid);

        // When checking uniqueness of existing entity, exclude its own record from the results
        if (!is_null($id)) {
            $qb->andWhere('s.id != :id')
                ->setParameter('id', $id);
        }

        $supplierExists = $qb->getQuery()->getOneOrNullResult();

        if ($supplierExists) {
            throw new InvalidArgumentException(
                sprintf(
                    'The Guid of the Supplier should be unique. This Guid is taken by: "%s"',
                    $supplierExists->getName()
                )
            );
        }
    }

    /**
     * @param $teamName
     * @param int|null $id
     * @throws InvalidArgumentException
     */
    private function isTeamNameUnique($teamName, $id = null)
    {
        $qb = $this->createQueryBuilder('s')
            ->where('s.teamName = :teamname')
            ->setParameter('teamname', $teamName);

        // When checking uniqueness of existing entity, exclude its own record from the results
        if (!is_null($id)) {
            $qb->andWhere('s.id != :id')
                ->setParameter('id', $id);
        }

        $supplierExists = $qb->getQuery()->getOneOrNullResult();

        if ($supplierExists) {
            throw new InvalidArgumentException(
                sprintf(
                    'The teamname of the Supplier should be unique. This teamname is taken by: "%s" with Guid: "%s"',
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

    /**
     * Find all suppliers for given team names.
     *
     * @param string[] $teamNames
     *
     * @return Supplier[]
     */
    public function findByTeamNames($teamNames)
    {
        return parent::findBy([
            'teamName' => $teamNames,
        ]);
    }
}
