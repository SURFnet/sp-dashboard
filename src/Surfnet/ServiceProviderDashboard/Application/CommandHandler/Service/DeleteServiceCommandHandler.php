<?php

declare(strict_types = 1);

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

namespace Surfnet\ServiceProviderDashboard\Application\CommandHandler\Service;

use Exception;
use League\Tactician\CommandBus;
use Psr\Log\LoggerInterface;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\DeleteCommandFactory;
use Surfnet\ServiceProviderDashboard\Application\Command\Service\DeleteServiceCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\CommandHandler;
use Surfnet\ServiceProviderDashboard\Application\Dto\EntityDto;
use Surfnet\ServiceProviderDashboard\Application\Exception\ServiceNotFoundException;
use Surfnet\ServiceProviderDashboard\Application\Service\EntityServiceInterface;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Contact;
use Surfnet\ServiceProviderDashboard\Domain\Repository\Invite\DeleteInviteRepository;
use Surfnet\ServiceProviderDashboard\Domain\Repository\ServiceRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\InviteException;

class DeleteServiceCommandHandler implements CommandHandler
{
    public function __construct(
        private readonly ServiceRepository $serviceRepository,
        private readonly EntityServiceInterface $entityService,
        private readonly DeleteCommandFactory $deleteCommandFactory,
        private readonly CommandBus $commandBus,
        private readonly DeleteInviteRepository $deleteInviteRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @throws InviteException
     * @throws ServiceNotFoundException
     */
    public function handle(DeleteServiceCommand $command): void
    {
        $serviceId = $command->getId();
        $service = $this->serviceRepository->findById($serviceId);

        if ($service === null) {
            throw new ServiceNotFoundException(sprintf('Could not delete service %s because it cannot be found.', $command->getId()));
        }

        $this->logger->info(sprintf('Removing "%s" and all its entities.', $service->getName()));

        // Remove the entities of the service
        $entities = $this->entityService->getEntitiesForService($service);
        $nofEntities = count($entities);
        if ($nofEntities > 0) {
            $this->logger->info(sprintf('Removing "%d" entities.', $nofEntities));
            // Invoke the correct entity delete command on the command bus
            $this->removeEntitiesFrom($entities, $command->getContact());
        }

        // Delete the role in Invite
        if ($service->getInviteRoleId() !== null) {
            $this->deleteInviteRepository->deleteRole($service->getInviteRoleId());
        }

        // Finally delete the service
        $this->serviceRepository->delete($service);
    }

    /**
     * Using the deleteCommandFactory, entity delete commands are created
     * that will remove them from the appropriate environment.
     *
     * @param EntityDto[] $entities
     */
    private function removeEntitiesFrom(array $entities, Contact $contact): void
    {
        foreach ($entities as $entity) {
            try {
                // Set the contact on the entity dto, it is required to create a jira ticket if need be
                $entity->setContact($contact);
                $command = $this->deleteCommandFactory->from($entity);
                $this->commandBus->handle($command);
            } catch (Exception $e) {
                $this->logger->error(
                    sprintf(
                        'Removing entity "%s" (env="%s", status="%s") failed',
                        $entity->getEntityId(),
                        $entity->getEnvironment(),
                        $entity->getState()
                    ),
                    [$e->getMessage()]
                );
            }
        }
    }
}
