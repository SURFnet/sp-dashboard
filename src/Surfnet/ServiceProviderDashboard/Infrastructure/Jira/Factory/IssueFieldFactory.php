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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\Jira\Factory;

use JiraRestApi\Issue\IssueField;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Ticket;
use Webmozart\Assert\Assert;

class IssueFieldFactory
{
    /**
     * @var string
     */
    private $assignee;

    /**
     * @var string
     */
    private $entityIdFieldName;

    /**
     * @var string
     */
    private $issueType;

    /**
     * @var string
     */
    private $priority;

    /**
     * @var string
     */
    private $projectKey;

    /**
     * @var string
     */
    private $reporter;

    /**
     * @param string $assignee
     * @param string $entityIdFieldName
     * @param string $issueType
     * @param string $priority
     * @param string $projectKey
     * @param string $reporter
     */
    public function __construct($assignee, $entityIdFieldName, $issueType, $priority, $projectKey, $reporter)
    {
        Assert::stringNotEmpty($assignee, 'The assignee may not be empty, configure in parameters.yml');
        Assert::stringNotEmpty(
            $entityIdFieldName,
            'The entity id field name may not be empty, configure in parameters.yml'
        );
        Assert::stringNotEmpty($issueType, 'The issue type may not be empty, configure in arameters.yml');
        Assert::stringNotEmpty($priority, 'The priority may not be empty, configure in parameters.yml');
        Assert::stringNotEmpty($projectKey, 'The project key may not be empty, configure in parameters.yml');
        Assert::stringNotEmpty($reporter, 'The reporter may not be empty, configure in parameters.yml');

        $this->assignee = $assignee;
        $this->entityIdFieldName = $entityIdFieldName;
        $this->issueType = $issueType;
        $this->priority = $priority;
        $this->projectKey = $projectKey;
        $this->reporter = $reporter;
    }

    public function fromTicket(Ticket $ticket)
    {
        $issueField = new IssueField();
        $issueField->setProjectKey($this->projectKey)
            ->setDescription($ticket->getDescription())
            ->setSummary($ticket->getSummary())
            ->setIssueType($this->issueType)
            ->setPriorityName($this->priority)
            ->setAssigneeName($this->assignee)
            ->setReporterName($this->reporter)
            ->addCustomField($this->entityIdFieldName, $ticket->getEntityId())
        ;

        return $issueField;
    }
}
