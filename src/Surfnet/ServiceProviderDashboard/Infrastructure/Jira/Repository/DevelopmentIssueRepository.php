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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\Jira\Repository;

use Surfnet\ServiceProviderDashboard\Application\Service\TicketServiceInterface;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Issue;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\IssueCollection;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Ticket;
use function array_key_exists;

class DevelopmentIssueRepository implements TicketServiceInterface
{
    /**
     * @var string
     */
    private $filePath;

    /**
     * @var Issue[]|null $data
     */
    private $data;

    /**
     * @param string $filePath
     */
    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    public function findByManageIds(array $manageIds)
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

    public function findByManageId($manageId)
    {
        $this->loadData();
        if (array_key_exists($manageId, $this->data)) {
            return $this->data[$manageId];
        }
        return null;
    }

    public function findByManageIdAndIssueType($manageId, $issueType)
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

    public function createIssueFrom(Ticket $ticket)
    {
        $this->loadData();
        $issue = new Issue($ticket->getManageId(), $ticket->getIssueType(), Issue::STATUS_OPEN);
        $this->data[$ticket->getManageId()] = $issue;
        $this->storeData();
        return $issue;
    }

    public function delete($issueKey)
    {
        $this->loadData();
        unset($this->data[$issueKey]);
        $this->storeData();
    }

    private function storeData()
    {
        file_put_contents($this->filePath, json_encode($this->data));
    }

    private function loadData()
    {
        if (!is_null($this->data)) {
            return;
        }

        if (!is_file($this->filePath)) {
            file_put_contents($this->filePath, '{}');
        }
        $rawData = json_decode(file_get_contents($this->filePath), true);
        if (is_null($rawData)) {
            $rawData = [];
        }
        $this->data = $this->loadIssues($rawData);
    }

    private function loadIssues(array $rawData)
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
