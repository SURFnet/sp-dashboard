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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client;

use Psr\Log\LoggerInterface;
use Surfnet\ServiceProviderDashboard\Application\Metadata\JsonGeneratorStrategy;
use Surfnet\ServiceProviderDashboard\Application\ViewObject\Apis\ApiConfig;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Contact;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\HttpException\HttpException;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\PublishMetadataException;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\PushMetadataException;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\HttpClientInterface;
use Surfnet\ServiceProviderDashboard\Domain\Repository\PublishEntityRepository
    as PublishEntityRepositoryInterface;

class PublishEntityClient implements PublishEntityRepositoryInterface
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly JsonGeneratorStrategy $generator,
        private readonly ApiConfig $manageConfig,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @throws PublishMetadataException
     *
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function publish(
        ManageEntity $entity,
        ?ManageEntity $pristineEntity,
        Contact $contact,
        string $part = ''
    ): mixed {
        try {
            if (!$entity->isManageEntity()) {
                $this->logger->info(sprintf('Creating new entity \'%s\' in manage', $entity->getId()));
                $response = $this->client->post(
                    json_encode(
                        $this->generator->generateForNewEntity(
                            $entity,
                            $this->manageConfig->getPublicationStatus()->getStatus(),
                            $contact,
                        )
                    ),
                    '/manage/api/internal/metadata'
                );
            } else {
                $diff = $pristineEntity->diff($entity);
                $this->logger->info(sprintf('Updating existing \'%s\' entity in manage', $entity->getId()));

                $data = json_encode(
                    $this->generator->generateForExistingEntity(
                        $entity,
                        $diff,
                        $this->manageConfig->getPublicationStatus()->getStatus(),
                        $part
                    )
                );

                $response = $this->client->put(
                    $data,
                    '/manage/api/internal/merge'
                );
            }

            if (!isset($response['id'])) {
                throw new PublishMetadataException('Unable to publish the metadata to Manage');
            }

            return $response;
        } catch (HttpException $e) {
            throw new PublishMetadataException('Unable to publish the metadata to Manage', 0, $e);
        }
    }

    /**
     * @throws PushMetadataException
     */
    public function pushMetadata(): mixed
    {
        try {
            $response = $this->client->read(
                '/manage/api/internal/push',
                [],
                ['Content-Type' => 'application/json']
            );
        } catch (HttpException $e) {
            $this->logger->error(
                'Unable to push to Engineblock',
                $response ?? []
            );
            throw new PushMetadataException('Unable to push the metadata to Engineblock', 0, $e);
        }

        if ($response['status'] != "OK") {
            $this->logger->error(
                'Manage rejected the push to Engineblock',
                $response ?? []
            );
            throw new PushMetadataException('Pushing did not succeed.');
        }
        return $response;
    }
}
