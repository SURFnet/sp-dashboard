<?php

/**
 * Copyright 2019 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Application\Metadata;

use Surfnet\ServiceProviderDashboard\Application\Exception\JsonGeneratorStrategyNotFoundException;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Dto\ManageEntity;

/**
 * Determines which strategy to use when generating Manage json metadata
 */
class JsonGeneratorStrategy
{
    /**
     * @var GeneratorInterface[] $strategies Keyed on the supported entity types
     */
    private $strategies;

    /**
     * Add a strategy
     *
     * Called from JsonGeneratorStrategyCompilerPass during compile time
     *
     * @param string $identifier
     * @param GeneratorInterface $generator
     */
    public function addStrategy($identifier, GeneratorInterface $generator)
    {
        $this->strategies[$identifier] = $generator;
    }

    /**
     * @param Entity $entity
     * @param string $workflowState
     * @return array
     * @throws JsonGeneratorStrategyNotFoundException
     */
    public function generateForNewEntity(Entity $entity, $workflowState)
    {
        return $this->getStrategy($entity->getProtocol())->generateForNewEntity($entity, $workflowState);
    }

    /**
     * @param Entity $entity
     * @param ManageEntity $manageEntity
     * @param string $workflowState
     * @return array
     * @throws JsonGeneratorStrategyNotFoundException
     */
    public function generateForExistingEntity(Entity $entity, ManageEntity $manageEntity, $workflowState)
    {
        return $this->getStrategy($entity->getProtocol())->generateForExistingEntity($entity, $manageEntity, $workflowState);
    }

    /**
     * @param $protocol
     * @return GeneratorInterface
     * @throws JsonGeneratorStrategyNotFoundException
     */
    private function getStrategy($protocol)
    {
        if (!isset($this->strategies[$protocol])) {
            throw new JsonGeneratorStrategyNotFoundException(
                sprintf('The requested JsonGenerator for protocol "%s" is not registered', $protocol)
            );
        }
        return $this->strategies[$protocol];
    }
}
