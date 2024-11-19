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

namespace Surfnet\ServiceProviderDashboard\Application\CommandHandler\Entity;

use JiraRestApi\JiraException;
use Psr\Log\LoggerInterface;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\RequestDeletePublishedEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\CommandHandler;
use Surfnet\ServiceProviderDashboard\Application\Service\TicketService;
use Surfnet\ServiceProviderDashboard\Domain\Repository\QueryManageRepository;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Ticket;
use Symfony\Component\HttpFoundation\RequestStack;
use Webmozart\Assert\Assert;

/**
 * @SuppressWarnings(PHPMD.LongClassName)
 */
class RequestDeletePublishedEntityCommandHandler implements CommandHandler
{
    private string $summaryTranslationKey = 'entity.delete.request.ticket.summary';

    private string $descriptionTranslationKey = 'entity.delete.request.ticket.description';

    public function __construct(
        private readonly QueryManageRepository $queryClient,
        private readonly string $issueType,
        private readonly TicketService $ticketService,
        private readonly RequestStack $requestStack,
        private readonly LoggerInterface $logger,
    ) {
        Assert::stringNotEmpty($issueType, 'Please set "jira_issue_type" in .env');
    }

    public function handle(RequestDeletePublishedEntityCommand $command): void
    {
        $this->logger->info(
            sprintf(
                'Request delete of a published production entity with manage id "%s"',
                $command->getManageId()
            )
        );
        $entity = $this->queryClient->findByManageId($command->getManageId());
        $ticket = Ticket::fromManageResponse(
            $entity,
            $command->getApplicant(),
            $this->issueType,
            $this->summaryTranslationKey,
            $this->descriptionTranslationKey
        );
        try {
            $issue = $this->ticketService->createIssueFrom($ticket);
            $this->logger->info(sprintf('Created Jira issue with key: %s', $issue->getKey()));
        } catch (JiraException $e) {
            $this->logger->critical('Unable to create the Jira issue.', [$e->getMessage()]);
            $this->requestStack->getSession()->getFlashBag()->add('error', 'entity.delete.request.failed');
        }
    }
}
