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
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\PublishProductionCommandInterface;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\CommandHandler;
use Surfnet\ServiceProviderDashboard\Application\Service\EntityServiceInterface;
use Surfnet\ServiceProviderDashboard\Application\Service\MailService;
use Surfnet\ServiceProviderDashboard\Application\Service\TicketService;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\Repository\PublishEntityRepository;
use Surfnet\ServiceProviderDashboard\Domain\Service\ContractualBaseService;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\PublishMetadataException;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PublishEntityProductionCommandHandler implements CommandHandler
{
    private readonly string $summaryTranslationKey;

    private readonly string $descriptionTranslationKey;

    public function __construct(
        private readonly PublishEntityRepository $publishClient,
        private readonly ContractualBaseService $contractualBaseHelper,
        private readonly EntityServiceInterface $entityService,
        private readonly TicketService $ticketService,
        private readonly RequestStack $requestStack,
        private readonly MailService $mailService,
        private readonly LoggerInterface $logger,
        private readonly string $issueType,
    ) {
        if (empty($issueType)) {
            throw new Exception('Please set "jira_issue_type_publication_request" in .env');
        }
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
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function handle(PublishProductionCommandInterface $command): void
    {
        $entity = $command->getManageEntity();
        $pristineEntity = null;
        if ($entity->isManageEntity()) {
            // The entity as it is now known in Manage
            $pristineEntity = $this->entityService->getPristineManageEntityById($entity->getId(), $entity->getEnvironment());
        }
        try {
            $this->logger->info(
                sprintf(
                    'Publishing entity "%s" to Manage in production environment',
                    $entity->getMetaData()->getNameEn()
                )
            );
            $this->contractualBaseHelper->writeContractualBase($entity);
            $publishResponse = $this->publishClient->publish(
                $entity,
                $pristineEntity,
                $command->getApplicant(),
            );
            if (array_key_exists('id', $publishResponse)) {
                $entity->setId($publishResponse['id']);
                // No need to create a Jira ticket when resetting the client secret
                if ($command instanceof PublishEntityProductionCommand && !$command->isClientReset()) {
                    $this->ticketService->createJiraTicket(
                        $entity,
                        $command,
                        $this->issueType,
                        $this->summaryTranslationKey,
                        $this->descriptionTranslationKey
                    );
                }
                $this->logger->info(
                    sprintf(
                        'Updating status of "%s" to published',
                        $entity->getMetaData()->getNameEn()
                    )
                );
            } else {
                $this->logger->error(
                    sprintf(
                        'Publishing to Manage failed for: "%s". Message: "%s"',
                        $entity->getMetaData()->getNameEn(),
                        'Manage did not return an id. See the context for more details.'
                    ),
                    [$publishResponse]
                );
                $this->requestStack->getSession()->getFlashBag()->add('error', 'entity.edit.error.publish');
            }
            if ($this->isNewResourceServer($entity)) {
                $this->requestStack->getSession()->getFlashBag()->add('wysiwyg', 'entity.list.oidcng_connection.info.html');
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
            $this->requestStack->getSession()->getFlashBag()->add('error', 'entity.edit.error.publish');
        } catch (Exception $e) {
            $this->logger->critical('Unable to create the Jira issue.', [$e->getMessage()]);
            $this->mailService->sendErrorReport($entity, $e);

            // Customer is presented an error message with the invitation to try again at a later stage
            $this->requestStack->getSession()->getFlashBag()->add('error', 'entity.edit.error.publish');
            return;
        }
    }

    private function isNewResourceServer(ManageEntity $entity): bool
    {
        $isNewEntity = empty($entity->getId());
        return $isNewEntity
            &&
            $entity->getProtocol()->getProtocol() === Constants::TYPE_OPENID_CONNECT_TNG_RESOURCE_SERVER;
    }
}
