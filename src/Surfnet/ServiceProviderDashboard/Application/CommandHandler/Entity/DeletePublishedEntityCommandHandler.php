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
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\DeletePublishedEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\CommandHandler;
use Surfnet\ServiceProviderDashboard\Application\Exception\EntityNotDeletedException;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Application\Exception\UnableToDeleteEntityException;
use Surfnet\ServiceProviderDashboard\Domain\Repository\DeleteEntityRepository;

class DeletePublishedEntityCommandHandler implements CommandHandler
{
    /**
     * @var DeleteEntityRepository
     */
    private $deleteEntityTest;

    /**
     * @var DeleteEntityRepository
     */
    private $deleteEntityProduction;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        DeleteEntityRepository $deleteEntityTest,
        DeleteEntityRepository $deleteEntityProduction,
        LoggerInterface $logger
    ) {
        $this->deleteEntityTest = $deleteEntityTest;
        $this->deleteEntityProduction = $deleteEntityProduction;
        $this->logger = $logger;
    }

    public function handle(DeletePublishedEntityCommand $command)
    {
        $this->logger->info(
            sprintf(
                'Removing entity with manage id "%s" from environment "%s"',
                $command->getManageId(),
                $command->getEnvironment()
            )
        );

        try {
            switch ($command->getEnvironment()) {
                case 'test':
                    $response = $this->deleteEntityTest->delete($command->getManageId());
                    break;
                case 'production':
                    $response = $this->deleteEntityProduction->delete($command->getManageId());
                    break;
                default:
                    throw new InvalidArgumentException(
                        sprintf('Deleting entities from "%s" environment is not supported.', $command->getEnvironment())
                    );
                    break;
            }
        } catch (UnableToDeleteEntityException $e) {
            throw new EntityNotDeletedException(
                sprintf(
                    'Deleting of entity with manage id "%s" from environment "%s" failed.',
                    $command->getManageId(),
                    $command->getEnvironment()
                ),
                0,
                $e
            );
        }

        if ($response !== DeleteEntityRepository::RESULT_SUCCESS) {
            throw new EntityNotDeletedException('Deleting the entity yielded an non success response');
        }
    }
}
