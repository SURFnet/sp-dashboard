<?php

//declare(strict_types = 1);

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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\Jira\Service;

use Psr\Log\LoggerInterface;
use Surfnet\ServiceProviderDashboard\Domain\Repository\TicketRepository;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Ticket;
use Surfnet\ServiceProviderDashboard\Infrastructure\Jira\Factory\IssueFieldFactory;
use Surfnet\ServiceProviderDashboard\Infrastructure\Jira\Factory\JiraServiceFactory;

class IssueService implements TicketRepository
{
    public function __construct(
        private readonly JiraServiceFactory $factory,
        private readonly IssueFieldFactory $fieldFactory,
        private readonly LoggerInterface $logger
    ) {
    }

    public function createIssue(Ticket $ticket): \JiraRestApi\Issue\Issue
    {
        $this->logger->info("Creating a Jira issue.");

        $issueField = $this->fieldFactory->fromTicket($ticket);

        return $this->factory
            ->buildIssueService()
            ->create($issueField);
    }
}
