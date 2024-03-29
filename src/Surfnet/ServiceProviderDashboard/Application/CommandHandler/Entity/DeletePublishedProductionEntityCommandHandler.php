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

namespace Surfnet\ServiceProviderDashboard\Application\CommandHandler\Entity;

use Psr\Log\LoggerInterface;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\DeletePublishedProductionEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\CommandHandler;
use Surfnet\ServiceProviderDashboard\Application\Exception\EntityNotDeletedException;
use Surfnet\ServiceProviderDashboard\Application\Exception\UnableToDeleteEntityException;
use Surfnet\ServiceProviderDashboard\Domain\Repository\DeleteManageEntityRepository;

/**
 * @SuppressWarnings(PHPMD.LongClassName)
 */
class DeletePublishedProductionEntityCommandHandler implements CommandHandler
{
    public function __construct(
        private readonly DeleteManageEntityRepository $deleteEntityRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function handle(DeletePublishedProductionEntityCommand $command): void
    {
        $this->logger->info(
            sprintf(
                'Removing entity with manage id "%s" from production environment',
                $command->getManageId()
            )
        );

        try {
            $response = $this->deleteEntityRepository->delete($command->getManageId(), $command->getProtocol());
        } catch (UnableToDeleteEntityException $e) {
            throw new EntityNotDeletedException(
                sprintf(
                    'Deleting of entity with manage id "%s" from production environment failed.',
                    $command->getManageId()
                ),
                0,
                $e
            );
        }

        if ($response !== DeleteManageEntityRepository::RESULT_SUCCESS) {
            throw new EntityNotDeletedException('Deleting the entity yielded a non success response');
        }
    }
}
