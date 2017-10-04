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
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Repository\ServiceRepository as ServiceRepositoryInterface;

class ServiceRepository extends EntityRepository implements ServiceRepositoryInterface
{
    /**
     * @param Service $service
     */
    public function save(Service $service)
    {
        $this->_em->persist($service);
        $this->_em->flush($service);
    }

    /**
     * @param int $id
     *
     * @return bool
     */
    public function isUnique($id)
    {
        $service = $this->createQueryBuilder('s')
            ->where('s.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
        if (is_null($service)) {
            return true;
        }

        return false;
    }

    /**
     * @param int $id
     * @return Service|null
     */
    public function findById($id)
    {
        return $this->find($id);
    }
}
