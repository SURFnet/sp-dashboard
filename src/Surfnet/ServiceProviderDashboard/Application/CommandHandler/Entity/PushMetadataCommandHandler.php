<?php

/**
 * Copyright 2020 SURFnet B.V.
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
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\PushMetadataCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\CommandHandler;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\PushMetadataException;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Service\ManagePublishService;
use Symfony\Component\HttpFoundation\RequestStack;

class PushMetadataCommandHandler implements CommandHandler
{
    public function __construct(
        private readonly ManagePublishService $publishService,
        private readonly RequestStack $requestStack,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function handle(PushMetadataCommand $command): void
    {
        $this->logger->info(
            sprintf(
                'Pushing metadata to EngineBlock using the %s environment.',
                $command->targetEnvironment()
            )
        );

        try {
            $this->publishService->pushMetadata($command->targetEnvironment());
        } catch (PushMetadataException $e) {
            $this->logger->error(sprintf('Pushing to EngineBlock failed with message: "%s"', $e->getMessage()));
            $this->requestStack->getSession()->getFlashBag()->add('error', 'entity.edit.error.push');
        }
    }
}
