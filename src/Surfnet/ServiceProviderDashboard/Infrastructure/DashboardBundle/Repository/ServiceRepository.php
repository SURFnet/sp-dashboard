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

use Doctrine\ORM\EntityRepository as DoctrineEntityRepository;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Repository\ServiceRepository as ServiceRepositoryInterface;

class ServiceRepository extends DoctrineEntityRepository implements ServiceRepositoryInterface
{
    /**
     * @param Service $service
     */
    public function save(Service $service): void
    {
        $this->getEntityManager()->persist($service);
        $this->getEntityManager()->flush($service);
    }

    /**
     * @param  Service $service
     * @return bool
     */
    public function isUnique(Service $service): bool
    {
        $this->isTeamNameUnique($service->getTeamName(), $service->getId());
        return true;
    }

    /**
     * @param  $teamName
     * @param  int|null $id
     * @throws InvalidArgumentException
     */
    private function isTeamNameUnique($teamName, $id = null): void
    {
        $qb = $this->createQueryBuilder('s')
            ->where('s.teamName = :teamname')
            ->setParameter('teamname', $teamName);

        // When checking uniqueness of existing entity, exclude its own record from the results
        if (!is_null($id)) {
            $qb->andWhere('s.id != :id')
                ->setParameter('id', $id);
        }

        $serviceExists = $qb->getQuery()->getOneOrNullResult();

        if ($serviceExists) {
            throw new InvalidArgumentException(
                sprintf(
                    'The teamname of the service should be unique. This teamname is taken by: "%s"',
                    $serviceExists->getName()
                )
            );
        }
    }

    /**
     * @param  int $id
     * @return Service|null
     */
    public function findById($id)
    {
        return $this->find($id);
    }

    /**
     * Find a service by name.
     *
     * @param string $name
     *
     * @return Service|null
     */
    public function findByName($name)
    {
        $services = parent::findBy(
            [
            'name' => $name,
            ]
        );

        if (empty($services)) {
            return null;
        } elseif (count($services) > 1) {
            throw new InvalidArgumentException(
                "Found multiple services with name '{$name}'"
            );
        }

        return reset($services);
    }

    /**
     * Find all services for given team names.
     *
     * @param string[] $teamNames
     *
     * @return Service[]
     */
    public function findByTeamNames($teamNames)
    {
        return parent::findBy(
            [
            'teamName' => $teamNames,
            ]
        );
    }

    public function delete(Service $service): void
    {
        $this->getEntityManager()->remove($service);
        $this->getEntityManager()->flush($service);
    }

    public function findByTeamName(?string $serviceTeamName): ?Service
    {
        return parent::findOneBy(
            [
            'teamName' => $serviceTeamName,
            ]
        );
    }
}
