<?php

declare(strict_types = 1);

/**
 * Copyright 2022 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Domain\Repository;

use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\JiraTicketNumber;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Protocol;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Contact;

interface EntityChangeRequestRepository
{
    /**
     * Open an entity change request in manage
     */
    public function openChangeRequest(
        ManageEntity $entity,
        ?ManageEntity $pristineEntity,
        Contact $contact,
        JiraTicketNumber $jiraTicketNumber,
    ): array;

    /**
     * Get outstanding change request from manage
     */
    public function getChangeRequest(string $id, Protocol $protocol): array;
}
