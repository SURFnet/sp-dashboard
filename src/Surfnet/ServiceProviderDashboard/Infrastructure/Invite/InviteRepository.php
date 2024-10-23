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
use Surfnet\ServiceProviderDashboard\Domain\Repository\InviteRepository as InviteRepositoryInterface;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\CreateRoleResponse;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\InviteException;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\RuntimeException;

readonly class InviteRepository implements InviteRepositoryInterface
{
    public function __construct(
        private InviteHttpClient $client,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @throws InviteException
     */
    public function createRole(
        string $name,
        string $shortName,
        string $description,
        string $landingPage,
        string $manageId,
    ): CreateRoleResponse {
        $team = $this->createPayload($name, $shortName, $description, $landingPage, $manageId);

        try {
            $this->logger->info(sprintf('Creating new role \'%s\' in invite', $name));

            $response = $this->client->post('/internal/invite/roles', $team);
            return (new InviteResponseFactory)->createFromResponse($response, $name);
        } catch (RuntimeException $e) {
            $this->logger->error(get_class($e) . ': ' . $e->getMessage());
            throw new InviteException(
                $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * @return array<mixed>
     */
    private function createPayload(
        string $name,
        string $shortName,
        string $description,
        string $landingPage,
        string $manageId,
    ): array {
        return [
            "name" => $name,
            "shortName" => $shortName,
            "description" => $description,
            "defaultExpiryDays" => 365,
            "applicationUsages" => [
                [
                    "landingPage" => $landingPage,
                    "application" => [
                        "manageId" => $manageId,
                        "manageType" => "SAML20_SP"
                    ]
                ]
            ]
        ];
    }
}
