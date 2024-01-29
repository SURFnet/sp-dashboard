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
namespace Surfnet\ServiceProviderDashboard\Application\ViewObject;

use ArrayIterator;
use IteratorAggregate;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;

class EntityList implements IteratorAggregate
{
    public function __construct(
        private array $entities,
    ) {
    }

    /**
     * @return Entity[]
     */
    public function getEntities(): array
    {
        return $this->entities;
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->entities);
    }

    public function hasTestEntities() : bool
    {
        foreach ($this->getEntities() as $entity) {
            if ($entity->getEnvironment() === Constants::ENVIRONMENT_TEST
                && $entity->getState() === Constants::STATE_PUBLISHED
            ) {
                return true;
            }
        }

        return false;
    }

    public function sortEntitiesByEnvironment(): static
    {
        $sortEntitiesByEnvironment = function (Entity $first, Entity $second): int {
            $envFirst = $first->getEnvironment();
            $envSecond = $second->getEnvironment();

            if ($envFirst === $envSecond) {
                return ($first->getName() < $second->getName()) ? -1 : 1;
            }

            return ($envFirst < $envSecond) ? -1 : 1;
        };
        usort($this->entities, $sortEntitiesByEnvironment);

        return $this;
    }
}
