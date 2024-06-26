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

namespace Surfnet\ServiceProviderDashboard\Application\Service;

use Surfnet\ServiceProviderDashboard\Application\Command\Entity\PublishProductionCommandInterface;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Issue;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\IssueCollection;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Ticket;

interface TicketServiceInterface
{
    /**
     * Create a Jira issue from a Ticket VO
     *
     * @return Issue|object
     */
    public function createIssueFrom(Ticket $ticket);

    /**
     * Create a Jira issue from a connection request
     *
     * @return Issue|object
     */
    public function createIssueFromConnectionRequest(Ticket $ticket);

    /**
     * Query Jira for issues that have a manageId that matches the provided array of manage id's.
     *
     * @return IssueCollection
     */
    public function findByManageIds(array $manageIds);

    /**
     * @param  int $id
     * @return Issue|null
     */
    public function findByManageId($id);

    /**
     * Find a Jira issue by issue type and manage id.
     *
     * @param  string $manageId
     * @param  string $issueType
     * @return mixed
     */
    public function findByManageIdAndIssueType($manageId, $issueType);

    public function delete(string $issueKey);

    public function createJiraTicket(
        ManageEntity $entity,
        PublishProductionCommandInterface $command,
        string $issueType,
        string $summaryTranslationKey,
        string $descriptionTranslationKey,
    ): Issue;
}
