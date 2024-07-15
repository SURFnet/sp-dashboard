<?php

/**
 * Copyright 2019 SURFnet B.V.
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
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\UpdateEntityAclCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\CommandHandler;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\AllowedIdentityProviders;
use Surfnet\ServiceProviderDashboard\Domain\Entity\IdentityProvider;
use Surfnet\ServiceProviderDashboard\Domain\Repository\PublishEntityRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\PublishMetadataException;
use Symfony\Component\HttpFoundation\RequestStack;

class UpdateEntityAclCommandHandler implements CommandHandler
{
    public function __construct(
        private readonly PublishEntityRepository $publishClient,
        private readonly LoggerInterface $logger,
        private readonly RequestStack $requestStack,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function handle(UpdateEntityAclCommand $command): void
    {
        $this->logger->info(
            sprintf(
                'Publishing entity "%s" to Manage in test environment to update ACL',
                $command->getManageEntity()->getId()
            )
        );

        $entity = $command->getManageEntity();
        $idps = array_map(fn(IdentityProvider $idp): string => $idp->getEntityId(), $command->getSelected());
        $allowedIdps = new AllowedIdentityProviders($idps, $command->isSelectAll());
        $entity->getAllowedIdentityProviders()->merge($allowedIdps);
        try {
            $this->publishClient->publish($entity, $entity, $command->getApplicant(), 'ACL');
        } catch (PublishMetadataException $e) {
            $this->logger->error(
                sprintf(
                    'Publishing to Manage failed for: "%s". Message: "%s"',
                    $entity->getMetaData()->getNameEn(),
                    $e->getMessage()
                )
            );
            $this->requestStack->getSession()->getFlashBag()->add('error', 'entity.edit.error.publish');
        }
    }
}
