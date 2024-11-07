<?php

declare(strict_types = 1);

/**
 * Copyright 2021 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\Teams\Client;

use Psr\Log\LoggerInterface;
use Surfnet\ServiceProviderDashboard\Application\Exception\UnableToDeleteEntityException;
use Surfnet\ServiceProviderDashboard\Domain\Repository\DeleteTeamsEntityRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\HttpException\HttpException;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\RuntimeException;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\UnableToDeleteMembershipException;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\HttpClient;

class DeleteEntityClient implements DeleteTeamsEntityRepository
{
    public function __construct(
        private readonly HttpClient $client,
    ) {
    }

    /**
     * Delete a membership by the internal id
     *
     * When deleting the membership succeeded the success status is returned: 'success' in all other situations
     * an exception is thrown of type UnableToDeleteEntityException.
     *
     * @throws UnableToDeleteEntityException
     * @throws RuntimeException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function deleteMembership(int $memberId): string
    {
        try {
            $result = $this->client->delete(
                sprintf('/api/spdashboard/memberships/%d', $memberId)
            );

            if ($result->getStatusCode() !== 201) {
                throw new UnableToDeleteMembershipException(
                    sprintf('Not allowed to delete member with internal teams ID: "%d"', $memberId)
                );
            }

            return self::RESULT_SUCCESS;
        } catch (HttpException $e) {
            throw new UnableToDeleteMembershipException(
                sprintf('Unable to delete member with internal teams ID: "%s"', $memberId),
                0,
                $e
            );
        }
    }
}
