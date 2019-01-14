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

use Psr\Log\LoggerInterface;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\PublishEntityProductionCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\CommandHandler;
use Surfnet\ServiceProviderDashboard\Application\Exception\NotAuthenticatedException;
use Surfnet\ServiceProviderDashboard\Application\Service\TicketService;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Repository\EntityRepository;
use Surfnet\ServiceProviderDashboard\Domain\Repository\PublishEntityRepository;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Ticket;
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
     * PublishEntityProductionCommandHandler constructor.
     * @param EntityRepository $entityRepository
     * @param PublishEntityRepository $publishClient
     * @param TicketService $ticketService
     * @param FlashBagInterface $flashBag
     * @param LoggerInterface $logger
     * @param string $issueType
     */
    public function __construct(
        EntityRepository $entityRepository,
        PublishEntityRepository $publishClient,
        TicketService $ticketService,
        FlashBagInterface $flashBag,
        LoggerInterface $logger,
        $issueType
    ) {
        Assert::stringNotEmpty($issueType, 'Please set "jira_issue_type_publication_request" in parameters.yml');
        $this->repository = $entityRepository;
        $this->publishClient = $publishClient;
        $this->ticketService = $ticketService;
        $this->flashBag = $flashBag;
        $this->logger = $logger;
        $this->issueType = $issueType;
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
     */
    public function handle(PublishEntityProductionCommand $command)
    {
        $entity = $this->repository->findById($command->getId());

        try {
            $this->logger->info(
                sprintf('Publishing entity "%s" to Manage in production environment', $entity->getNameEn())
            );

            $publishResponse = $this->publishClient->publish($entity);

            if (array_key_exists('id', $publishResponse)) {
                // Send the confirmation mail
                $this->logger->info(
                    sprintf('Sending publish request mail to service desk for "%s".', $entity->getNameEn())
                );

                $ticket = Ticket::fromEntity($entity, $command->getApplicant(), $this->issueType);
                try {
                    $issue = $this->ticketService->createIssueFrom($ticket);
                    $this->logger->info(sprintf('Created Jira issue with key: %s', $issue->key));
                } catch (JiraException $e) {
                    $this->logger->critical('Unable to create the Jira issue.', [$e->getMessage()]);
                }
            }
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

        // Set entity status to published
        $entity->setStatus(Entity::STATE_PUBLISHED);
        $this->logger->info(sprintf('Updating status of "%s" to published.', $entity->getNameEn()));

        // Save changes made to entity
        $this->repository->save($entity);
    }
}
