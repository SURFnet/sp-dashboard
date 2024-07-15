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

use Surfnet\ServiceProviderDashboard\Domain\Entity\Contact;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\JiraTicketNumber;
use Surfnet\ServiceProviderDashboard\Domain\Entity\EntityDiff;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;

interface GeneratorInterface
{
    /**
     * Convert a new, unpublished entity to json-serializable array.
     */
    public function generateForNewEntity(
        ManageEntity $entity,
        string $workflowState,
        Contact $contact,
    ): array;

    /**
     * Convert entity to an array for the manage merge-write API call.
     *
     * The resulting array is almost identical to the one created by
     * generateNew(), but only contains fields stored in SP-dashboard, and
     * never overwrites fields not managed by the dashboard (such as allowed
     * entities).
     *
     * The updatedPart parameter currently is only used when updating the ACL.
     * It was introduced as part of fixing the bug described here: https://www.pivotaltracker.com/story/show/178461498.
     * The idea is that it'll be used in the future for all updates.
     */
    public function generateForExistingEntity(
        ManageEntity $entity,
        EntityDiff $differences,
        string $workflowState,
        string $updatedPart = '',
    ): array;

    /**
     * Generate an Entity Change Request in Manage
     *
     * This should only be applied on published production entities. But should work
     * on other publication states too.
     */
    public function generateEntityChangeRequest(
        ManageEntity $entity,
        EntityDiff $differences,
        Contact $contact,
        JiraTicketNumber $jiraTicketNumber,
    ): array;
}
