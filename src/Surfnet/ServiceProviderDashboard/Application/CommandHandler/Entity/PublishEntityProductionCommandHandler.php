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

use Exception;
use Psr\Log\LoggerInterface;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\PublishEntityProductionCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\CommandHandler;
use Surfnet\ServiceProviderDashboard\Application\Exception\NotAuthenticatedException;
use Surfnet\ServiceProviderDashboard\Application\Service\TicketService;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Mailer\Mailer;
use Surfnet\ServiceProviderDashboard\Domain\Repository\EntityRepository;
use Surfnet\ServiceProviderDashboard\Domain\Repository\PublishEntityRepository;
use Surfnet\ServiceProviderDashboard\Domain\Repository\ServiceRepository;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Ticket;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Factory\MailMessageFactory;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Exception\PublishMetadataException;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Webmozart\Assert\Assert;

class PublishEntityProductionCommandHandler implements CommandHandler
{
    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * @var EntityRepository
     */
    private $serviceRepository;

    /**
     * @var PublishEntityRepository
     */
    private $publishClient;
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
     * @var MailMessageFactory
     */
    private $mailFactory;

    /**
     * @var Mailer
     */
    private $mailer;

    /**
     * @var string
     */
    private $summaryTranslationKey;

    /**
     * @var string
     */
    private $descriptionTranslationKey;

    /**
     * @param EntityRepository $entityRepository
     * @param ServiceRepository $serviceRepository
     * @param PublishEntityRepository $publishClient
     * @param TicketService $ticketService
     * @param FlashBagInterface $flashBag
     * @param MailMessageFactory $mailFactory
     * @param Mailer $mailer
     * @param LoggerInterface $logger
     * @param string $issueType
     */
    public function __construct(
        EntityRepository $entityRepository,
        ServiceRepository $serviceRepository,
        PublishEntityRepository $publishClient,
        TicketService $ticketService,
        FlashBagInterface $flashBag,
        MailMessageFactory $mailFactory,
        Mailer $mailer,
        LoggerInterface $logger,
        $issueType
    ) {
        Assert::stringNotEmpty($issueType, 'Please set "jira_issue_type_publication_request" in parameters.yml');
        $this->repository = $entityRepository;
        $this->serviceRepository = $serviceRepository;
        $this->publishClient = $publishClient;
        $this->ticketService = $ticketService;
        $this->mailFactory = $mailFactory;
        $this->mailer = $mailer;
        $this->flashBag = $flashBag;
        $this->logger = $logger;
        $this->issueType = $issueType;
        $this->summaryTranslationKey = 'entity.publish.request.ticket.summary';
        $this->descriptionTranslationKey = 'entity.publish.request.ticket.description';
    }

    /**
     * Publishes the entity to production
     *
     * Some remarks:
     *  - The production manage connection is used to publish to production
     *  - In addition to a test publish; the coin:exclude_from_push attribute is passed with value 1
     *  - A jira ticket is created to inform the service desk of the pending publication request
     *
     * @param PublishEntityProductionCommand $command
     * @throws NotAuthenticatedException
     *
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function handle(PublishEntityProductionCommand $command)
    {
        $entity = $this->repository->findById($command->getId());

        // 1. Create the Jira ticket
        $ticket = Ticket::fromEntity(
            $entity,
            $command->getApplicant(),
            $this->issueType,
            $this->summaryTranslationKey,
            $this->descriptionTranslationKey
        );

        $this->logger->info(
            sprintf('Creating a %s Jira issue for "%s".', $this->issueType, $entity->getNameEn())
        );

        try {
            $issue = null;
            if ($entity->getManageId()) {
                // Before creating an issue, test if we didn't previously create this ticket (users can apply changes to
                // requested published entities).
                $issue = $this->ticketService->findByManageIdAndIssueType($entity->getManageId(), $this->issueType);
            }
            if (is_null($issue)) {
                $issue = $this->ticketService->createIssueFrom($ticket);
                $this->logger->info(sprintf('Created Jira issue with key: %s', $issue->key));
            } else {
                $this->logger->info(sprintf('Found existing Jira issue with key: %s', $issue->key));
            }
        } catch (Exception $e) {
            $this->logger->critical('Unable to create the Jira issue.', [$e->getMessage()]);

            // Inform the service desk of the unavailability of Jira
            $message = $this->mailFactory->buildJiraIssueFailedMessage($e, $entity);
            $this->mailer->send($message);

            // Customer is presented an error message with the invitation to try again at a later stage
            $this->flashBag->add('error', 'entity.edit.error.publish');
            // Stop execution
            return;
        }

        // 2. On success, publish the entity to production
        try {
            $this->logger->info(
                sprintf('Publishing entity "%s" to Manage in production environment', $entity->getNameEn())
            );
            $publishResponse = $this->publishClient->publish($entity);
            if (array_key_exists('id', $publishResponse)) {
                // Set entity status to published
                $entity->setStatus(Entity::STATE_PUBLISHED);

                // Also update the service status to requested, but only if current status is not-requested
                $service = $entity->getService();
                if ($service->getConnectionStatus() == Service::CONNECTION_STATUS_NOT_REQUESTED) {
                    $service->setConnectionStatus(Service::CONNECTION_STATUS_REQUESTED);
                    $this->serviceRepository->save($service);
                }

                $this->logger->info(sprintf('Updating status of "%s" to published', $entity->getNameEn()));
                // Save changes made to entity
                $this->repository->save($entity);
            } else {
                $this->logger->error(
                    sprintf(
                        'Publishing to Manage failed for: "%s". Message: "%s"',
                        $entity->getNameEn(),
                        'Manage did not return an id. See the context for more details.'
                    ),
                    [$publishResponse]
                );
                $this->flashBag->add('error', 'entity.edit.error.publish');
            }

            return;
        } catch (PublishMetadataException $e) {
            $this->logger->error(
                sprintf(
                    'Publishing to Manage failed for: "%s". Message: "%s"',
                    $entity->getNameEn(),
                    $e->getMessage()
                )
            );
            $this->flashBag->add('error', 'entity.edit.error.publish');
        }

        // 3. On failure, remove the Jira ticket that was previously created. The user must retry at a later stage
        $this->logger->info(sprintf('Deleting Jira issue with key: %s after failed publication action', $issue->key));
        $this->ticketService->delete($issue->key);
    }
}
