<?php

declare(strict_types = 1);

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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\Jira\Repository;

use JiraRestApi\JiraException;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\PublishProductionCommandInterface;
use Surfnet\ServiceProviderDashboard\Application\Service\TicketServiceInterface;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Issue;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\IssueCollection;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Ticket;

class DevelopmentIssueRepository implements TicketServiceInterface
{
    /**
     * @var Issue[] $data
     */
    private $data = [];
    /**
     * @var bool
     */
    private $failIssueCreation = false;

    public function __construct(private readonly string $filePath)
    {
    }

    public function shouldFailCreateIssue(): void
    {
        $this->loadData();
        $this->failIssueCreation = true;
        $this->storeData();
    }

    public function findByManageIds(array $manageIds): IssueCollection
    {
        $this->loadData();
        $result = [];
        foreach ($this->data as $manageId => $issue) {
            if (in_array($manageId, $manageIds)) {
                $result[$manageId] = $issue;
            }
        }
        return new IssueCollection($result);
    }

    public function createJiraTicket(
        ManageEntity $entity,
        PublishProductionCommandInterface $command,
        string $issueType,
        string $summaryTranslationKey,
        string $descriptionTranslationKey
    ): Issue {
        return new Issue('KEY-27', 'fake-type', Issue::STATUS_OPEN);
    }

    public function findByManageId($manageId): ?\Surfnet\ServiceProviderDashboard\Domain\ValueObject\Issue
    {
        $this->loadData();
        if (array_key_exists($manageId, $this->data)) {
            return $this->data[$manageId];
        }
        return null;
    }

    public function findByManageIdAndIssueType($manageId, $issueType): ?\Surfnet\ServiceProviderDashboard\Domain\ValueObject\Issue
    {
        $this->loadData();
        if (array_key_exists($manageId, $this->data)) {
            $issue = $this->data[$manageId];
            if ($issue->getIssueType() == $issueType) {
                return $issue;
            }
        }
        return null;
    }

    public function createIssueFrom(Ticket $ticket): Issue
    {
        $this->loadData();
        if ($this->failIssueCreation) {
            throw new JiraException('Unable to write the Jira issue (failure was requested by calling shouldFailCreateIssue)');
        }
        $issue = new Issue($ticket->getManageId(), $ticket->getIssueType(), Issue::STATUS_OPEN);
        $this->data[$ticket->getManageId()] = $issue;
        $this->storeData();
        return $issue;
    }

    public function createIssueFromConnectionRequest(Ticket $ticket): Issue
    {
        $this->loadData();
        if ($this->failIssueCreation) {
            throw new JiraException('Unable to write the Jira issue (failure was requested by calling shouldFailCreateIssue)');
        }
        $issue = new Issue($ticket->getManageId(), $ticket->getIssueType(), Issue::STATUS_OPEN);
        $this->data[$ticket->getManageId()] = $issue;
        $this->storeData();
        return $issue;
    }

    public function delete($issueKey): void
    {
        $this->loadData();
        unset($this->data[$issueKey]);
        $this->storeData();
    }

    private function storeData(): void
    {
        file_put_contents(
            $this->filePath,
            json_encode(
                [
                    'data' => $this->data,
                    'failIssueCreation' => $this->failIssueCreation
                ]
            )
        );
    }

    private function loadData(): void
    {
        if (!is_file($this->filePath)) {
            file_put_contents($this->filePath, '{}');
        }
        $rawData = json_decode(file_get_contents($this->filePath), true);
        if (is_null($rawData)) {
            $rawData = [];
        }

        if (!array_key_exists('failIssueCreation', $rawData)) {
            $rawData['failIssueCreation'] = false;
        }

        if (!array_key_exists('data', $rawData)) {
            $rawData['data'] = [];
        }

        $this->failIssueCreation = $rawData['failIssueCreation'];
        $this->data = $this->loadIssues($rawData['data']);
    }

    /**
     * @return mixed[]
     */
    private function loadIssues(array $rawData): array
    {
        $output = [];
        foreach ($rawData as $issueData) {
            if (!array_key_exists(Issue::IDENTIFIER_TICKET_STATUS, $issueData)) {
                $issueData[Issue::IDENTIFIER_TICKET_STATUS] = Issue::STATUS_OPEN;
            }
            $output[$issueData['key']] = Issue::fromSerializedData($issueData);
        }
        return $output;
    }
}
