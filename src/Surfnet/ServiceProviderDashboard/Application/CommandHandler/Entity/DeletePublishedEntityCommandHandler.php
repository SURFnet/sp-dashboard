<?php

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

namespace Surfnet\ServiceProviderDashboard\Application\CommandHandler\Entity;

use Psr\Log\LoggerInterface;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\DeletePublishedEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\CommandHandler;
use Surfnet\ServiceProviderDashboard\Domain\Repository\EntityRepository;

class DeletePublishedEntityCommandHandler implements CommandHandler
{
    /**
     * @var EntityRepository
     */
    private $entityRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(EntityRepository $entityRepository, LoggerInterface $logger)
    {
        $this->entityRepository = $entityRepository;
        $this->logger = $logger;
    }

    public function handle(DeletePublishedEntityCommand $command)
    {
        $entity = $this->entityRepository->findById($command->getId());
        $this->logger->info(
            sprintf(
                'Removing entity "%s" after successfully publishing it to the service registry',
                $entity->getNameEn()
            )
        );
        $this->entityRepository->delete($entity);
    }
}
