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

namespace Surfnet\ServiceProviderDashboard\Application\CommandHandler\Service;

use Psr\Log\LoggerInterface;
use Surfnet\ServiceProviderDashboard\Application\Command\Service\PublishServiceCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\CommandHandler;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Domain\Repository\PublishServiceRepository;
use Surfnet\ServiceProviderDashboard\Domain\Repository\ServiceRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Exception\PublishMetadataException;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Exception\PushMetadataException;

class PublishServiceCommandHandler implements CommandHandler
{
    /**
     * @var ServiceRepository
     */
    private $repository;

    /**
     * @var PublishServiceRepository
     */
    private $publishClient;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ServiceRepository $serviceRepository
     * @param PublishServiceRepository $publishClient
     */
    public function __construct(
        ServiceRepository $serviceRepository,
        PublishServiceRepository $publishClient,
        LoggerInterface $logger
    ){
        $this->repository = $serviceRepository;
        $this->publishClient = $publishClient;
        $this->logger = $logger;
    }

    /**
     * @param PublishServiceCommand $command
     *
     * @throws InvalidArgumentException
     */
    public function handle(PublishServiceCommand $command)
    {
        $service = $this->repository->findById($command->getId());
        try {
            $this->logger->info(sprintf('Publishing service "%s" to Manage in test environment', $service->getNameNl()));
            $publishResponse = $this->publishClient->publish($service);

            if (array_key_exists('id', $publishResponse)) {
                $this->logger->info(sprintf('Pushing service "%s" to engineblock', $service->getNameNl()));
                $this->publishClient->pushMetadata();
            }
        } catch (PublishMetadataException $e) {
            $this->logger->error(
                sprintf(
                    'Publishing to Manage failed for: "%s". Message: "%s"',
                    $service->getNameNl(),
                    $e->getMessage()
                )
            );
            // Todo: Inform end user?

            // Todo: Inform servicedesk by email?
        } catch (PushMetadataException $e) {
            $this->logger->error(sprintf('Pushing to Engineblock failed with message: ', $e->getMessage()));
            // Todo: Inform end user?

            // Todo: Inform servicedesk by email?
        }
    }
}
