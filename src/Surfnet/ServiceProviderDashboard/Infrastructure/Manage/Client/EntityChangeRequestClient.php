<?php

declare(strict_types = 1);

/**
 * Copyright 2022 SURFnet B.V.
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
use Surfnet\ServiceProviderDashboard\Domain\Entity\Contact;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Protocol;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\Repository\EntityChangeRequestRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\PublishMetadataException;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\HttpClientInterface;

class EntityChangeRequestClient implements EntityChangeRequestRepository
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly JsonGeneratorStrategy $generator,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function openChangeRequest(
        ManageEntity $entity,
        ?ManageEntity $pristineEntity,
        Contact $contact,
        string $jiraTicketNumber
    ): array {
        $this->logger->info(sprintf('Creating entity change request in manage for entity "%s"', $entity->getId()));

        $diff = $pristineEntity->diff($entity);
        $payload = json_encode(
            $this->generator->generateEntityChangeRequest($entity, $diff, $contact, $jiraTicketNumber)
        );
        $response = $this->client->post(
            $payload,
            '/manage/api/internal/change-requests'
        );

        if (!isset($response['id'])) {
            throw new PublishMetadataException('Unable to open a entity change request in Manage');
        }

        return $response;
    }

    public function getChangeRequest(string $id, Protocol $protocol): array
    {
        $this->logger->info(sprintf('Get outstanding change requests from manage for entity "%s"', $id));

        return $this->client->read(
            sprintf('/manage/api/internal/change-requests/%s/%s', $protocol->getManagedProtocol(), $id)
        );
    }
}
