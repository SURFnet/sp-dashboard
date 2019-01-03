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
use Surfnet\ServiceProviderDashboard\Application\Metadata\GeneratorInterface;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Repository\PublishEntityRepository as PublishEntityRepositoryInterface;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Exception\PublishMetadataException;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Exception\PushMetadataException;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Http\Exception\HttpException;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Http\HttpClient;

class PublishEntityClient implements PublishEntityRepositoryInterface
{
    /**
     * @var HttpClient
     */
    private $client;

    /**
     * @var GeneratorInterface
     */
    private $generator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(HttpClient $client, GeneratorInterface $generator, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->generator = $generator;
        $this->logger = $logger;
    }

    /**
     * @param Entity $entity
     * @return mixed
     * @throws PublishMetadataException
     *
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function publish(Entity $entity)
    {
        try {
            if (empty($entity->getManageId())) {
                $this->logger->info(sprintf('Creating new entity \'%s\' in manage', $entity->getEntityId()));

                $response = $this->client->post(
                    json_encode($this->generator->generateForNewEntity($entity)),
                    '/manage/api/internal/metadata'
                );
            } else {
                $this->logger->info(sprintf('Updating existing \'%s\' entity in manage', $entity->getEntityId()));

                $response = $this->client->put(
                    json_encode($this->generator->generateForExistingEntity($entity)),
                    '/manage/api/internal/merge'
                );
            }

            if (isset($response['status']) && $response['status'] == 400) {
                throw new PublishMetadataException('Unable to publish the metadata to Manage');
            }

            return $response;
        } catch (HttpException $e) {
            throw new PublishMetadataException('Unable to publish the metadata to Manage', 0, $e);
        }
    }

    /**
     * @return mixed
     *
     * @throws PushMetadataException
     */
    public function pushMetadata()
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
                (isset($response)) ? $response : []
            );
            throw new PushMetadataException('Unable to push the metadata to Engineblock', 0, $e);
        }

        if ($response['status'] != "OK") {
            $this->logger->error(
                'Manage rejected the push to Engineblock',
                (isset($response)) ? $response : []
            );
            throw new PushMetadataException('Pushing did not succeed.');
        }
        return $response;
    }
}
