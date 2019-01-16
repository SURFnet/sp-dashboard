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
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Ticket;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\QueryClient as ManageQueryClient;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class RequestDeletePublishedEntityCommandHandler implements CommandHandler
{
    /**
     * @var TicketService
     */
    private $ticketService;

    /**
     * @var ManageQueryClient
     */
    private $queryClient;

    /**
     * @var FlashBagInterface
     */
    private $flashBag;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ManageQueryClient $manageProductionQueryClient,
        TicketService $ticketService,
        FlashBagInterface $flashBag,
        LoggerInterface $logger
    ) {
        $this->queryClient = $manageProductionQueryClient;
        $this->ticketService = $ticketService;
        $this->flashBag = $flashBag;
        $this->logger = $logger;
    }

    public function handle(RequestDeletePublishedEntityCommand $command)
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
            $command->getIssueType(),
            $command->getIssueSummaryTranslationKey(),
            $command->getIssueDescriptionTranslationKey()
        );
        try {
            $issue = $this->ticketService->createIssueFrom($ticket);
            $this->logger->info(sprintf('Created Jira issue with key: %s', $issue->key));
        } catch (JiraException $e) {
            $this->logger->critical('Unable to create the Jira issue.', [$e->getMessage()]);
            $this->flashBag->add('error', 'entity.delete.request.failed');
        }
    }
}
