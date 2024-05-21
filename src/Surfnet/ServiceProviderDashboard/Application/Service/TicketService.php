<?php

declare(strict_types = 1);

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

namespace Surfnet\ServiceProviderDashboard\Application\Service;

use Psr\Log\LoggerInterface;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\CreateConnectionRequestCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\PublishProductionCommandInterface;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Issue;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\IssueCollection;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Ticket;

class TicketService implements TicketServiceInterface
{
    public function __construct(
        private readonly TicketServiceInterface $issueRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function createIssueFrom(Ticket $ticket)
    {
        return $this->issueRepository->createIssueFrom($ticket);
    }

    public function createIssueFromConnectionRequest(Ticket $ticket)
    {
        return $this->issueRepository->createIssueFromConnectionRequest($ticket);
    }

    public function findByManageIds(array $manageIds): IssueCollection
    {
        return $this->issueRepository->findByManageIds($manageIds);
    }

    public function findByManageId($id): ?Issue
    {
        return $this->issueRepository->findByManageId($id);
    }

    public function delete(string $issueKey): void
    {
        $this->issueRepository->delete($issueKey);
    }

    public function findByManageIdAndIssueType($manageId, $issueType)
    {
        return $this->issueRepository->findByManageIdAndIssueType($manageId, $issueType);
    }

    public function createJiraTicket(
        ManageEntity $entity,
        PublishProductionCommandInterface $command,
        string $issueType,
        string $summaryTranslationKey,
        string $descriptionTranslationKey,
    ): Issue {
        $ticket = $this->createTicketFromManageResponse(
            $command,
            $entity,
            $issueType,
            $summaryTranslationKey,
            $descriptionTranslationKey
        );

        $this->logger->info(
            sprintf('Creating a %s Jira issue for "%s".', $issueType, $entity->getMetaData()->getNameEn())
        );
        $issue = null;
        if ($entity->getId()) {
            // Before creating an issue, test if we didn't previously create this ticket (users can apply changes to
            // requested published entities).
            $issue = $this->findByManageIdAndIssueType($entity->getId(), $issueType);
        }
        if (is_null($issue)) {
            $issue = $this->createIssueFrom($ticket);
            $this->logger->info(sprintf('Created Jira issue with key: %s', $issue->getKey()));
            return $issue;
        }
        $this->logger->info(sprintf('Found existing Jira issue with key: %s', $issue->getKey()));
        return $issue;
    }

    public function createJiraTicketForConnectionRequests(
        CreateConnectionRequestCommand $command,
        string $issueType,
        string $summaryTranslationKey,
        string $descriptionTranslationKey,
    ): Issue {
    
        $ticket = Ticket::fromConnectionRequests(
            $command->getManageEntity(),
            $command->getApplicant(),
            $command->getConnectionRequests(),
            $issueType,
            $summaryTranslationKey,
            $descriptionTranslationKey
        );
        return $this->createIssueFromConnectionRequest($ticket);
    }

    private function createTicketFromManageResponse(
        PublishProductionCommandInterface $command,
        ManageEntity $entity,
        string $issueType,
        string $summaryTranslationKey,
        string $descriptionTranslationKey,
    ): Ticket {
        return Ticket::fromManageResponse(
            $entity,
            $command->getApplicant(),
            $issueType,
            $summaryTranslationKey,
            $descriptionTranslationKey
        );
    }
}
