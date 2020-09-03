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
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\Repository\EntityRepository;
use Surfnet\ServiceProviderDashboard\Domain\Repository\PublishEntityRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\QueryClient;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Exception\PublishMetadataException;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class PublishEntityTestCommandHandler implements CommandHandler
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

    /**
     * @var QueryClient
     */
    private $manageQueryClient;

    public function __construct(
        PublishEntityRepository $publishClient,
        QueryClient $queryClient,
        LoggerInterface $logger,
        FlashBagInterface $flashBag
    ) {
        $this->publishClient = $publishClient;
        $this->manageQueryClient = $queryClient;
        $this->logger = $logger;
        $this->flashBag = $flashBag;
    }

    /**
     * @param PublishEntityTestCommand $command
     *
     * @throws InvalidArgumentException
     */
    public function handle(PublishEntityTestCommand $command)
    {
        $entity = $command->getManageEntity();
        try {
            $this->logger->info(
                sprintf(
                    'Publishing entity "%s" to Manage in test environment',
                    $entity->getMetaData()->getNameNl()
                )
            );

            $publishResponse = $this->publishClient->publish($entity);

            if (array_key_exists('id', $publishResponse)) {
                if ($this->isNewResourceServer($entity)) {
                    $this->flashBag->add('wysiwyg', 'entity.list.oidcng_connection.info.html');
                }
            }
        } catch (PublishMetadataException $e) {
            $this->logger->error(
                sprintf(
                    'Publishing to Manage failed for: "%s". Message: "%s"',
                    $entity->getNameNl(),
                    $e->getMessage()
                )
            );
            $this->flashBag->add('error', 'entity.edit.error.publish');
        }
    }

    private function isNewResourceServer(ManageEntity $entity)
    {
        $isNewEntity = empty($entity->getId());
        return $isNewEntity && $entity->getProtocol() === Constants::TYPE_OPENID_CONNECT_TNG_RESOURCE_SERVER;
    }
}
