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

use Surfnet\ServiceProviderDashboard\Application\Service\TicketServiceInterface;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Issue;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\IssueCollection;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Ticket;
use Surfnet\ServiceProviderDashboard\Infrastructure\Jira\Factory\IssueFieldFactory;
use Surfnet\ServiceProviderDashboard\Infrastructure\Jira\Factory\JiraServiceFactory;
use Webmozart\Assert\Assert;

class IssueRepository implements TicketServiceInterface
{
    /**
     * @var JiraServiceFactory
     */
    private $jiraFactory;

    /**
     * @var IssueFieldFactory
     */
    private $issueFactory;

    /**
     * @var string
     */
    private $projectKey;

    /**
     * @var string
     */
    private $issueType;

    /**
     * @var string
     */
    private $manageIdFieldName;

    /**
     * @var string
     */
    private $manageIdFieldLabel;

    /**
     * @param JiraServiceFactory $jiraFactory
     * @param IssueFieldFactory $issueFactory
     * @param string $projectKey
     * @param string $issueType
     * @param string $manageIdFieldName
     * @param string $manageIdFieldLabel
     */
    public function __construct(
        JiraServiceFactory $jiraFactory,
        IssueFieldFactory $issueFactory,
        $projectKey,
        $issueType,
        $manageIdFieldName,
        $manageIdFieldLabel
    ) {
        Assert::stringNotEmpty($projectKey, 'Please set "jira_issue_project_key" in parameters.yml');
        Assert::stringNotEmpty($manageIdFieldName, 'Please set "jira_issue_manageid_fieldname" in parameters.yml');
        Assert::stringNotEmpty(
            $manageIdFieldLabel,
            'Please set "jira_issue_manageid_field_label" in parameters.yml'
        );

        $this->jiraFactory = $jiraFactory;
        $this->issueFactory = $issueFactory;
        $this->projectKey = $projectKey;
        $this->issueType = $issueType;
        $this->manageIdFieldName = $manageIdFieldName;
        $this->manageIdFieldLabel = $manageIdFieldLabel;
    }

    public function findByManageIds(array $manageIds)
    {
        $issueService = $this->jiraFactory->buildIssueService();
        // Search all CTX: spd-delete-production-entity issues
        $issues = $issueService->search(
            sprintf('project = %s AND issuetype = %s', $this->projectKey, $this->issueType)
        );
        $collection = [];
        foreach ($issues->issues as $issue) {
            $manageId = $issue->fields->customFields[$this->manageIdFieldName];
            if (in_array($manageId, $manageIds)) {
                $collection[$manageId] = new Issue($issue->key, $this->issueType);
            }
        }
        return new IssueCollection($collection);
    }

    public function findByManageId($manageId)
    {
        $issueService = $this->jiraFactory->buildIssueService();
        // Search CTX: spd-delete-production-entity issues with manage id as provided in the $manageId parameter
        $issues = $issueService->search(
            sprintf(
                'project = %s AND issuetype = %s AND "%s" ~ %s',
                $this->projectKey,
                $this->issueType,
                $this->manageIdFieldLabel,
                $manageId
            )
        );
        if ($issues->getTotal() > 0) {
            return new Issue($issues->getIssue(0)->key, $this->issueType);
        }
        return null;
    }

    public function findByManageIdAndIssueType($manageId, $issueType)
    {
        $issueService = $this->jiraFactory->buildIssueService();
        // Search CTX: "$issueType" issues with manage id as provided in the $manageId parameter
        $issues = $issueService->search(
            sprintf(
                'project = %s AND issuetype = %s AND "%s" ~ %s',
                $this->projectKey,
                $issueType,
                $this->manageIdFieldLabel,
                $manageId
            )
        );
        if ($issues->getTotal() > 0) {
            return new Issue($issues->getIssue(0)->key, $issueType);
        }
        return null;
    }

    public function createIssueFrom(Ticket $ticket)
    {
        $issueField = $this->issueFactory->fromTicket($ticket);
        $issueService = $this->jiraFactory->buildIssueService();
        $issue = $issueService->create($issueField);
        return new Issue($issue->key, $ticket->getIssueType());
    }

    public function delete($issueKey)
    {
        $issueService = $this->jiraFactory->buildIssueService();
        $issueService->deleteIssue($issueKey);
    }
}
