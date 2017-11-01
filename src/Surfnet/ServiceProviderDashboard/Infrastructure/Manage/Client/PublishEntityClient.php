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
     *
     * @param array $metadataFields
     * @return mixed
     * @throws PublishMetadataException
     */
    public function publish(Entity $entity, array $metadataFields)
    {
        // Once more generate the xml based on the latest values set on the entity
        $xmlMetadata = $this->generator->generate($entity);
        $json = json_encode(['xml' => $xmlMetadata]);

        try {
            $response = $this->client->post($json, '/manage/api/internal/new-sp');

            // Validation fails with response code 400
            if (isset($response['status']) && $response['status'] == 400) {
                $this->logger->error('Schema violations returned from Manage', $response);
                throw new PublishMetadataException('Unable to publish the metadata to Manage');
            }

            // We have a valid response, set the comment as a revision note
            $this->logger->info('Update entity, set comment as revision note');

            $pathUpdates = $metadataFields;

            // Send the metadata url (not imported in XML post)
            $pathUpdates = array_merge(
                $pathUpdates,
                ['metadataurl' => $entity->getMetadataUrl()]
            );

            if ($entity->hasComments()) {
                $pathUpdates = array_merge(
                    ['revisionnote' => $entity->getComments()],
                    $pathUpdates
                );
            }

            $update = json_encode([
                'id' => $response['id'],
                'type' => 'saml20_sp',
                'pathUpdates' => $pathUpdates,
            ]);

            $response = $this->client->put($update, 'manage/api/internal/merge');
            // Validation fails with response code 400
            if (isset($response['status']) && $response['status'] == 400) {
                $this->logger->error('Schema violations returned from Manage while updating data', $response);
                throw new PublishMetadataException('Unable to update data of the entity in Manage');
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
            throw new PushMetadataException('Unable to push the metadata to Engineblock', 0, $e);
        }

        if ($response['status'] != "OK") {
            throw new PushMetadataException('Pushing did not succeed.');
        }
        return $response;
    }
}
