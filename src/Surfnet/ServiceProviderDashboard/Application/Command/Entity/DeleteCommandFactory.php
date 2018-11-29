<?php

/**
 * Copyright 2018 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Application\Command\Entity;

use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Application\ViewObject\Entity;

/**
 * DeleteCommandFactory builds entity delete commands
 *
 * The from method can be use to let the factory determine what
 * sort of delete command is required. It will test the entity
 * for its environment and production state and select the correct
 * command to match the user intent.
 *
 * The commands themselves are also available for creation via
 * public methods on the factory instance.
 */
class DeleteCommandFactory
{
    public function from(Entity $entity)
    {
        $isDraft = $entity->getState() === 'draft';
        $isPublishedToTest = $entity->getEnvironment() === 'test' && $entity->getState() === 'published';
        $isPublishedProduction = $entity->getEnvironment() === 'production' && $entity->getState() === 'requested';
        $isRequestDelete = $entity->getEnvironment() === 'production' && $entity->getState() === 'published';

        if ($isDraft) {
            return $this->buildDeleteDraftEntityCommand($entity->getId());
        }
        if ($isPublishedToTest) {
            return $this->buildDeletePublishedTestEntityCommand($entity->getId());
        }
        if ($isPublishedProduction) {
            return $this->buildDeletePublishedProductionEntityCommand($entity->getId());
        }
        if ($isRequestDelete) {
            return $this->buildRequestDeletePublishedEntityCommand($entity->getId());
        }
        throw new InvalidArgumentException('This entity state/environment combination is not supported for deleting');
    }

    public function buildDeleteDraftEntityCommand($entityId)
    {
        return new DeleteDraftEntityCommand($entityId);
    }

    public function buildDeletePublishedTestEntityCommand($manageId)
    {
        return new DeletePublishedTestEntityCommand($manageId);
    }

    public function buildDeletePublishedProductionEntityCommand($manageId)
    {
        return new DeletePublishedProductionEntityCommand($manageId);
    }

    public function buildRequestDeletePublishedEntityCommand($manageId)
    {
        return new RequestDeletePublishedEntityCommand($manageId);
    }
}
