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

use Surfnet\ServiceProviderDashboard\Application\Dto\MetadataConversionDto;
use Surfnet\ServiceProviderDashboard\Application\Exception\JsonGeneratorStrategyNotFoundException;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Contact;
use Surfnet\ServiceProviderDashboard\Domain\Entity\EntityDiff;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;

/**
 * Determines which strategy to use when generating Manage json metadata
 */
class JsonGeneratorStrategy
{
    /**
     * @var GeneratorInterface[] $strategies Keyed on the supported entity types
     */
    private ?array $strategies = null;

    /**
     * Add a strategy
     *
     * Called from JsonGeneratorStrategyCompilerPass during compile time
     *
     * @param string $identifier
     */
    public function addStrategy($identifier, GeneratorInterface $generator): void
    {
        $this->strategies[$identifier] = $generator;
    }

    /**
     * @throws JsonGeneratorStrategyNotFoundException
     */
    public function generateForNewEntity(ManageEntity $entity, string $workflowState): array
    {
        return $this->getStrategy($entity->getProtocol()->getProtocol())->generateForNewEntity($entity, $workflowState);
    }

    /**
     * @throws JsonGeneratorStrategyNotFoundException
     */
    public function generateForExistingEntity(
        ManageEntity $entity,
        EntityDiff $differences,
        string $workflowState,
        string $updatedPart = '',
    ): array {
        return $this->getStrategy($entity->getProtocol()->getProtocol())
            ->generateForExistingEntity($entity, $differences, $workflowState, $updatedPart);
    }

    public function generateEntityChangeRequest(
        ManageEntity $entity,
        EntityDiff $differences,
        Contact $contact,
        string $jiraTicketNumber
    ): array {
        return $this
            ->getStrategy($entity->getProtocol()->getProtocol())
            ->generateEntityChangeRequest(
                $entity,
                $differences,
                $contact,
                $jiraTicketNumber
            );
    }

    /**
     * @param  $protocol
     * @return GeneratorInterface
     * @throws JsonGeneratorStrategyNotFoundException
     */
    private function getStrategy(?string $protocol)
    {
        if (!isset($this->strategies[$protocol])) {
            throw new JsonGeneratorStrategyNotFoundException(
                sprintf('The requested JsonGenerator for protocol "%s" is not registered', $protocol)
            );
        }
        return $this->strategies[$protocol];
    }
}
