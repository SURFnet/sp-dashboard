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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\Jira\Repository;

use Surfnet\ServiceProviderDashboard\Application\Command\Entity\PublishProductionCommandInterface;
use Surfnet\ServiceProviderDashboard\Application\Service\TicketServiceInterface;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Issue;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\IssueCollection;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Ticket;
use Surfnet\ServiceProviderDashboard\Infrastructure\Jira\Factory\IssueFieldFactory;
use Surfnet\ServiceProviderDashboard\Infrastructure\Jira\Factory\JiraServiceFactory;
use Webmozart\Assert\Assert;
use function array_key_exists;

/**
 * This file is a fake implementation that will be used by a compiler pass in test mode
 */
class IssueRepository implements TicketServiceInterface
{
    public function __construct(
        private readonly JiraServiceFactory $jiraFactory,
        private readonly IssueFieldFactory $issueFactory,
        private $projectKey,
        private $issueType,
        private $manageIdFieldName,
        private $manageIdFieldLabel,
    ) {
        Assert::stringNotEmpty($projectKey, 'Please set "jira_issue_project_key" in .env');
        Assert::stringNotEmpty($manageIdFieldName, 'Please set "jira_issue_manageid_fieldname" in .env');
        Assert::stringNotEmpty(
            $manageIdFieldLabel,
            'Please set "jira_issue_manageid_field_label" in .env'
        );
    }

    public function findByManageIds(array $manageIds): IssueCollection
    {
        $issueService = $this->jiraFactory->buildIssueService();
        // Search all CTX: spd-delete-production-entity issues
        $issues = $issueService->search(
            sprintf('project = %s AND resolution = Unresolved AND issuetype = %s', $this->projectKey, $this->issueType)
        );
        $collection = [];
        foreach ($issues->issues as $issue) {
            if (!array_key_exists($this->manageIdFieldName, $issue->fields->customFields)) {
                continue;
            }
            $manageId = $issue->fields->customFields[$this->manageIdFieldName];
            if (in_array($manageId, $manageIds)) {
                $collection[$manageId] = new Issue($issue->key, $this->issueType, $issue->fields->status->name);
            }
        }
        return new IssueCollection($collection);
    }

    public function findByManageId($manageId): ?Issue
    {
        $issueService = $this->jiraFactory->buildIssueService();
        // Search CTX: spd-delete-production-entity issues with manage id as provided in the $manageId parameter
        $issues = $issueService->search(
            sprintf(
                'project = %s AND resolution = Unresolved AND issuetype = %s AND "%s" ~ %s',
                $this->projectKey,
                $this->issueType,
                $this->manageIdFieldLabel,
                $manageId
            )
        );
        if ($issues->getTotal() > 0) {
            $issue = $issues->getIssue(0);
            return new Issue($issue->key, $this->issueType, $issue->fields->status->name);
        }
        return null;
    }

    public function findByManageIdAndIssueType($manageId, $issueType): ?Issue
    {
        $issueService = $this->jiraFactory->buildIssueService();
        // Search CTX: "$issueType" issues with manage id as provided in the $manageId parameter
        $issues = $issueService->search(
            sprintf(
                'project = %s AND issuetype = %s AND resolution = Unresolved AND "%s" ~ %s',
                $this->projectKey,
                $issueType,
                $this->manageIdFieldLabel,
                $manageId
            )
        );
        if ($issues->getTotal() > 0) {
            $issue = $issues->getIssue(0);
            return new Issue($issue->key, $issueType, $issue->fields->status->name);
        }
        return null;
    }

    public function createIssueFrom(Ticket $ticket): Issue
    {
        $issueField = $this->issueFactory->fromTicket($ticket);
        $issueService = $this->jiraFactory->buildIssueService();
        $issue = $issueService->create($issueField);
        return new Issue($issue->key, $ticket->getIssueType(), Issue::STATUS_OPEN);
    }

    public function createIssueFromConnectionRequest(Ticket $ticket): Issue
    {
        $issueField = $this->issueFactory->fromConnectionRequestTicket($ticket);
        $issueService = $this->jiraFactory->buildIssueService();
        $issue = $issueService->create($issueField);
        return new Issue($issue->key, $ticket->getIssueType(), Issue::STATUS_OPEN);
    }

    public function delete($issueKey): void
    {
        $issueService = $this->jiraFactory->buildIssueService();
        $issueService->deleteIssue($issueKey);
    }

    public function createJiraTicket(
        ManageEntity $entity,
        PublishProductionCommandInterface $command,
        string $issueType,
        string $summaryTranslationKey,
        string $descriptionTranslationKey,
    ): Issue {

        return new Issue('fake', 'fake', 'fake');
    }
}
