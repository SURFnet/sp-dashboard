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
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Exception\PublishMetadataException;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class UpdateEntityAclCommandHandler implements CommandHandler
{
    /**
     * @var PublishEntityRepository
     */
    private $publishClient;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var FlashBagInterface
     */
    private $flashBag;

    public function __construct(
        PublishEntityRepository $publishClient,
        LoggerInterface $logger,
        FlashBagInterface $flashBag
    ) {
        $this->publishClient = $publishClient;
        $this->logger = $logger;
        $this->flashBag = $flashBag;
    }

    /**
     * @param UpdateEntityAclCommand $command
     *
     * @throws InvalidArgumentException
     */
    public function handle(UpdateEntityAclCommand $command)
    {
        $this->logger->info(sprintf('Publishing entity "%s" to Manage in test environment to update ACL', $command->getManageEntity()->getId()));

        $entity = $command->getManageEntity();
        $idps = array_map(function (IdentityProvider $idp) {
            return $idp->getEntityId();
        }, $command->getSelected());
        $allowedIdps = new AllowedIdentityProviders($idps, $command->isSelectAll());
        $entity->getAllowedIdentityProviders()->merge($allowedIdps);
        try {
            $this->publishClient->publish($entity, 'ACL');
        } catch (PublishMetadataException $e) {
            $this->logger->error(
                sprintf(
                    'Publishing to Manage failed for: "%s". Message: "%s"',
                    $entity->getMetaData()->getNameEn(),
                    $e->getMessage()
                )
            );
            $this->flashBag->add('error', 'entity.edit.error.publish');
        }
    }
}
