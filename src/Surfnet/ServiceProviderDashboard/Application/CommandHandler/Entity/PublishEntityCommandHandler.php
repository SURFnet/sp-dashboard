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
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\PublishEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\CommandHandler;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Application\Factory\MotivationMetadataFactory;
use Surfnet\ServiceProviderDashboard\Application\Factory\PrivacyQuestionsMetadataFactory;
use Surfnet\ServiceProviderDashboard\Application\Factory\SpDashboardMetadataFactory;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Repository\EntityRepository;
use Surfnet\ServiceProviderDashboard\Domain\Repository\PublishEntityRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Exception\PublishMetadataException;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Exception\PushMetadataException;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class PublishEntityCommandHandler implements CommandHandler
{
    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * @var PublishEntityRepository
     */
    private $publishClient;

    /**
     * @var PrivacyQuestionsMetadataFactory
     */
    private $privacyQuestionsMetadataFactory;

    /**
     * @var MotivationMetadataFactory
     */
    private $motivationMetadataFactory;

    /**
     * @var SpDashboardMetadataFactory
     */
    private $spDashboardMetadataFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var FlashBagInterface
     */
    private $flashBag;

    public function __construct(
        EntityRepository $entityRepository,
        PublishEntityRepository $publishClient,
        PrivacyQuestionsMetadataFactory $privacyQuestionsMetadataFactory,
        MotivationMetadataFactory $motivationMetadataFactory,
        SpDashboardMetadataFactory $spDashboardMetadataFactory,
        LoggerInterface $logger,
        FlashBagInterface $flashBag
    ) {
        $this->repository = $entityRepository;
        $this->publishClient = $publishClient;
        $this->logger = $logger;
        $this->flashBag = $flashBag;

        $this->privacyQuestionsMetadataFactory = $privacyQuestionsMetadataFactory;
        $this->motivationMetadataFactory = $motivationMetadataFactory;
        $this->spDashboardMetadataFactory = $spDashboardMetadataFactory;
    }

    /**
     * @param PublishEntityCommand $command
     *
     * @throws InvalidArgumentException
     */
    public function handle(PublishEntityCommand $command)
    {
        $entity = $this->repository->findById($command->getId());
        try {
            $this->logger->info(sprintf('Publishing entity "%s" to Manage in test environment', $entity->getNameNl()));

            $publishResponse = $this->publishClient->publish($entity, $this->buildMetadataFields($entity));

            if (array_key_exists('id', $publishResponse)) {
                $this->logger->info(sprintf('Pushing entity "%s" to engineblock', $entity->getNameNl()));
                $this->publishClient->pushMetadata();
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
        } catch (PushMetadataException $e) {
            $this->logger->error(sprintf('Pushing to Engineblock failed with message: ', $e->getMessage()));
            $this->flashBag->add('error', 'entity.edit.error.push');
        }
    }

    /**
     * Builds and merges the different types of metadata fields that are set on the entity.
     *
     * @param Entity $entity
     * @return array
     */
    private function buildMetadataFields(Entity $entity)
    {
        $mergedMetadata = array_merge(
            $this->privacyQuestionsMetadataFactory->build($entity),
            $this->motivationMetadataFactory->build($entity),
            $this->spDashboardMetadataFactory->build($entity)
        );

        return $mergedMetadata;
    }
}
