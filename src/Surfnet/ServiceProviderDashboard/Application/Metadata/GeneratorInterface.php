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

namespace Surfnet\ServiceProviderDashboard\Application\Metadata;

use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Dto\ManageEntity;

interface GeneratorInterface
{
    /**
     * Convert a new, unpublished entity to json-serializable array.
     *
     * @param Entity $entity
     * @param string $workflowState
     * @return array
     */
    public function generateForNewEntity(Entity $entity, $workflowState);

    /**
     * Convert entity to an array for the manage merge-write API call.
     *
     * The resulting array is almost identical to the one created by
     * generateNew(), but only contains fields stored in SP-dashboard, and
     * never overwrites fields not managed by the dashboard (such as allowed
     * entities).
     *
     * @param Entity $entity
     * @param ManageEntity $manageEntity
     * @param string $workflowState
     * @return array
     */
    public function generateForExistingEntity(Entity $entity, ManageEntity $manageEntity, $workflowState);
}
