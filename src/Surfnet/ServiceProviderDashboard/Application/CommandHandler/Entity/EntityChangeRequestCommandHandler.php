<?php

/**
 * Copyright 2022 SURFnet B.V.
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

use Exception;
use Psr\Log\LoggerInterface;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\PublishProductionCommandInterface;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\CommandHandler;
use Surfnet\ServiceProviderDashboard\Application\Exception\EntityNotFoundException;
use Surfnet\ServiceProviderDashboard\Application\Service\EntityServiceInterface;
use Surfnet\ServiceProviderDashboard\Application\Service\MailService;
use Surfnet\ServiceProviderDashboard\Application\Service\TicketService;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\Repository\EntityChangeRequestRepository;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Ticket;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\PublishMetadataException;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class EntityChangeRequestCommandHandler implements CommandHandler
{
    /**
     * @var EntityChangeRequestRepository
     */
    private $repository;
    /**
     * @var TicketService
     */
    private $ticketService;

    /**
     * @var FlashBagInterface
     */
    private $flashBag;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $issueType;

    /**
     * @var MailService
     */
    private $mailService;

    /**
     * @var string
     */
    private $summaryTranslationKey;

    /**
     * @var string
     */
    private $descriptionTranslationKey;

    /**
     * @var EntityServiceInterface
     */
    private $entityService;

    public function __construct(
        EntityChangeRequestRepository $repository,
        EntityServiceInterface $entityService,
        TicketService $ticketService,
        FlashBagInterface $flashBag,
        MailService $mailService,
        LoggerInterface $logger,
        string $issueType
    ) {
        if (empty($issueType)) {
            throw new Exception('Please set "jira_issue_type_entity_change_request" in parameters.yml');
        }
        $this->repository = $repository;
        $this->entityService = $entityService;
        $this->ticketService = $ticketService;
        $this->mailService = $mailService;
        $this->flashBag = $flashBag;
        $this->logger = $logger;
        $this->issueType = $issueType;
        $this->summaryTranslationKey = 'entity.change_request.ticket.summary';
        $this->descriptionTranslationKey = 'entity.change_request.ticket.description';
    }

    /**
     * Creates an entity change request in Manage
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function handle(PublishProductionCommandInterface $command)
    {
        $entity = $command->getManageEntity();
        if (!$entity->isManageEntity()) {
            throw new EntityNotFoundException('Unable to request changes to a unkown entity in Manage');
        }
        $pristineEntity = $this->entityService->getManageEntityById($entity->getId(), $entity->getEnvironment());
        try {
            $this->logger->info(
                sprintf(
                    'Requesting changes in production environment for entity: "%s"',
                    $entity->getMetaData()->getNameEn()
                )
            );

            $response = $this->repository->openChangeRequest($entity, $pristineEntity, $command->getApplicant());
            if (array_key_exists('id', $response)) {
                $this->createJiraTicket($entity, $command);
            } else {
                $this->logger->error(
                    sprintf(
                        'Creating an entity change request in Manage failed for: "%s". Message: "%s"',
                        $entity->getMetaData()->getNameEn(),
                        'Manage did not return an id. See the context for more details.'
                    ),
                    [$response]
                );
                $this->flashBag->add('error', 'entity.edit.error.publish');
            }
            return;
        } catch (PublishMetadataException $e) {
            $this->logger->error(
                sprintf(
                    'Publishing to Manage failed for: "%s". Message: "%s"',
                    $entity->getMetaData()->getNameEn(),
                    $e->getMessage()
                )
            );
            $this->flashBag->add('error', 'entity.edit.error.publish');
        } catch (Exception $e) {
            $this->logger->critical('Unable to create the Jira issue.', [$e->getMessage()]);

            // Inform the service desk of the unavailability of Jira
            $this->mailService->sendErrorReport($entity, $e);

            // Customer is presented an error message with the invitation to try again at a later stage
            $this->flashBag->add('error', 'entity.edit.error.publish');
            return;
        }
    }

    private function createJiraTicket(ManageEntity $entity, PublishProductionCommandInterface $command)
    {
        $ticket = Ticket::fromManageResponse(
            $entity,
            $command->getApplicant(),
            $this->issueType,
            $this->summaryTranslationKey,
            $this->descriptionTranslationKey
        );

        $this->logger->info(
            sprintf('Creating a %s Jira issue for "%s".', $this->issueType, $entity->getMetaData()->getNameEn())
        );
        $issue = null;
        if ($entity->getId()) {
            // Before creating an issue, test if we didn't previously create this ticket (users can apply changes to
            // requested published entities).
            $issue = $this->ticketService->findByManageIdAndIssueType($entity->getId(), $this->issueType);
        }
        if (is_null($issue)) {
            $issue = $this->ticketService->createIssueFrom($ticket);
            $this->logger->info(sprintf('Created Jira issue with key: %s', $issue->getKey()));
            return $issue;
        }
        $this->logger->info(sprintf('Found existing Jira issue with key: %s', $issue->getKey()));
        return $issue;
    }
}
