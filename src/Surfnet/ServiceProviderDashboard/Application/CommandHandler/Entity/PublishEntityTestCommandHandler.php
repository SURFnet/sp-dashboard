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
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\PublishEntityTestCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\CommandHandler;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Application\Service\EntityServiceInterface;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\Repository\PublishEntityRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\PublishMetadataException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class PublishEntityTestCommandHandler implements CommandHandler
{
    public function __construct(
        private readonly PublishEntityRepository $publishClient,
        private readonly EntityServiceInterface $entityService,
        private readonly LoggerInterface $logger,
        private readonly RequestStack $requestStack,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function handle(PublishEntityTestCommand $command): void
    {
        $entity = $command->getManageEntity();
        $pristineEntity = null;
        if ($entity->isManageEntity()) {
            // The entity as it is now known in Manage
            $pristineEntity = $this->entityService->getPristineManageEntityById($entity->getId(), $entity->getEnvironment());
        }
        try {
            $this->logger->info(
                sprintf(
                    'Publishing entity "%s" to Manage in test environment',
                    $entity->getMetaData()->getNameEn()
                )
            );

            $publishResponse = $this->publishClient->publish($entity, $pristineEntity);

            if (array_key_exists('id', $publishResponse) && $this->isNewResourceServer($entity)) {
                $this->requestStack->getSession()->getFlashBag()->add('wysiwyg', 'entity.list.oidcng_connection.info.html');
            }
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

    private function isNewResourceServer(ManageEntity $entity): bool
    {
        $isNewEntity = empty($entity->getId());
        return $isNewEntity
            &&
            $entity->getProtocol()->getProtocol() === Constants::TYPE_OPENID_CONNECT_TNG_RESOURCE_SERVER;
    }
}
