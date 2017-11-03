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

use League\Tactician\CommandBus;
use Psr\Log\LoggerInterface;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\PublishEntityProductionCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Mail\PublishToProductionMailCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\CommandHandler;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Repository\EntityRepository;

class PublishEntityProductionCommandHandler implements CommandHandler
{
    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        EntityRepository $entityRepository,
        CommandBus $commandBus,
        LoggerInterface $logger
    ) {
        $this->repository = $entityRepository;
        $this->commandBus = $commandBus;
        $this->logger = $logger;
    }

    /**
     * @param PublishEntityProductionCommand $command
     *
     * @throws InvalidArgumentException
     */
    public function handle(PublishEntityProductionCommand $command)
    {
        $entity = $this->repository->findById($command->getId());
        $this->logger->info(sprintf('Sending publish request mail to servicedesk for "%s".', $entity->getNameEn()));

        // Send the confirmation mail
        $mailCommand = new PublishToProductionMailCommand($command->getMessage());
        $this->commandBus->handle($mailCommand);

        // Set entity status to published even though it is not realy published to manage
        $entity->setStatus(Entity::STATE_PUBLISHED);
        $this->logger->info(sprintf('Updating status of "%s" to published.', $entity->getNameEn()));
        $this->repository->save($entity);
    }
}
