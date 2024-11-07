<?php

/**
 * Copyright 2024 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\Invite;

use Psr\Log\LoggerInterface;
use Surfnet\ServiceProviderDashboard\Domain\Repository\Invite\DeleteInviteRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\InviteException;
use Throwable;

readonly class InviteDeleteRepository implements DeleteInviteRepository
{
    public function __construct(
        private InviteHttpClient $client,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @throws InviteException
     */
    public function deleteRole(int $roleId): void
    {
        try {
            $this->logger->info(sprintf('Deleting role with ID \'%s\' in invite', $roleId));

            $response = $this->client->delete('/external/v1/roles/' . $roleId);
            if ($response->getStatusCode() !== 200) {
                throw new InviteException(sprintf('Could not delete Role. Invite returned non 200 status code "%d"', $response->getStatusCode()));
            }
        } catch (Throwable $e) {
            $this->logger->error(get_class($e) . ': ' . $e->getMessage());
            throw new InviteException($e->getMessage(), 0, $e);
        }
    }
}
