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

namespace Surfnet\ServiceProviderDashboard\Application\CommandHandler\Service;

use Psr\Log\LoggerInterface;
use Surfnet\ServiceProviderDashboard\Application\Command\Service\DeleteServiceCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\CommandHandler;
use Surfnet\ServiceProviderDashboard\Application\Service\EntityServiceInterface;
use Surfnet\ServiceProviderDashboard\Domain\Repository\ServiceRepository;

class DeleteServiceCommandHandler implements CommandHandler
{
    /**
     * @var ServiceRepository
     */
    private $serviceRepository;

    /**
     * @var EntityServiceInterface
     */
    private $entityService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ServiceRepository $serviceRepository,
        EntityServiceInterface $entityService,
        LoggerInterface $logger
    ) {
        $this->serviceRepository = $serviceRepository;
        $this->entityService = $entityService;
        $this->logger = $logger;
    }

    /**
     * @param DeleteServiceCommand $command
     */
    public function handle(DeleteServiceCommand $command)
    {
        $serviceId = $command->getId();
        $service = $this->serviceRepository->findById($serviceId);

        $this->logger->info(sprintf('Removing "%s" and all its entities.', $service->getName()));

        // Remove the entities of the service
        $entityList = $this->entityService->getEntityListForService($service);
        $nofEntities = count($entityList->getEntities());
        if ($nofEntities > 0) {
            $this->logger->info(sprintf('Removing "%d" entities.', $nofEntities));
            $this->entityService->removeFrom($entityList);
        }

        // Finally delete the service
        $this->serviceRepository->delete($service);
    }
}
