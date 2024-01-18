<?php

//declare(strict_types = 1);

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

use GuzzleHttp\Exception\GuzzleException;
use Surfnet\ServiceProviderDashboard\Domain\Repository\QueryTeamsRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\HttpException\HttpException;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\QueryServiceProviderException;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\RuntimeException;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\HttpClientInterface;
use function sprintf;

/**
 * The QueryClient can be used to perform queries on the manage /api/spdashboard/teams/<urn> endpoint
 *
 * Queries will return the full team information.
 *
 * Example response (json formatted for readability)
 *  {
        "id": 1,
        "urn": "demo:openconext:org:riders",
        "name": "riders",
        "description": "we are riders",
        "personalNote": null,
        "viewable": true,
        "created": 1630333540,
        "publicLink": "wZiomLDTk3CU2FR9bRy1IFCfYSqt5AFwSAs74M1EuIQs3D",
        "publicLinkDisabled": false,
        "membershipCount": 2,
        "memberships": [
            {
                "id": 1,
                "role": "ADMIN",
                "created": 1630333540,
                "person": {
                    "id": 1,
                    "urn": "urn:collab:person:surfnet.nl:jdoe",
                    "name": "John Doe",
                    "email": "john.doe@example.org",
                    "created": 1630333540,
                    "lastLoginDate": 1630333540,
                    "guest": false,
                    "superAdmin": false
                },
                "urnPerson": "urn:collab:person:surfnet.nl:jdoe",
                "urnTeam": "demo:openconext:org:riders",
                "expiryDate": null,
                "origin": "INITIAL_ADMIN",
                "approvedBy": "John Doe"
            },
            {
                "id": 10,
                "role": "MEMBER",
                "created": 1630333540,
                "person": {
                    "id": 5,
                    "urn": "urn:collab:person:surfnet.nl:rdoe",
                    "name": "Ronald Doe",
                    "email": "ronald.doe@example.org",
                    "created": 1630333540,
                    "lastLoginDate": 1630333540,
                    "guest": false,
                    "superAdmin": false
                },
                "urnPerson": "urn:collab:person:surfnet.nl:rdoe",
                "urnTeam": "demo:openconext:org:riders",
                "expiryDate": null,
                "origin": null,
                "approvedBy": null
            },
            {
                "id": 13,
                "role": "OWNER",
                "created": 1630333540,
                "person": {
                    "id": 8,
                    "urn": "urn:collab:person:example.com:owner",
                    "name": "Owner User",
                    "email": "owner@domain.net",
                    "created": 1630333540,
                    "lastLoginDate": 1526542332,
                    "guest": false,
                    "superAdmin": false
                },
                "urnPerson": "urn:collab:person:example.com:owner",
                "urnTeam": "demo:openconext:org:riders",
                "expiryDate": null,
                "origin": null,
                "approvedBy": null
            }
        ],
        "invitations": [
            {
                "id": 1,
                "email": "test@example.com",
                "timestamp": 2491484828910,
                "declined": false,
                "accepted": false,
                "invitationMessages": [
                    {
                        "id": 1,
                        "message": "Please join",
                        "timestamp": 2491484828910
                    }
                ],
                "intendedRole": "MANAGER",
                "language": "DUTCH",
                "expiryDate": 1506636000,
                "daysValid": 9997,
                "expired": false
            }
        ],
    "joinRequests": null,
    "externalTeams": null
}
 */
class QueryClient implements QueryTeamsRepository
{
    public function __construct(private readonly HttpClientInterface $client)
    {
    }

    /**
     * @throws QueryServiceProviderException
     * @throws GuzzleException
     * @throws RuntimeException
     */
    public function findTeamByUrn(string $urn): ?array
    {
        try {
            $result = $this->client->read(
                sprintf('/api/spdashboard/teams/%s', $urn)
            );

            if (!empty($result)) {
                return $this->transformTeamForFrontend($result);
            }

            return null;
        } catch (HttpException $e) {
            throw new QueryServiceProviderException(
                sprintf('Unable to find entity with urn: "%s"', $urn),
                0,
                $e
            );
        }
    }

    private function transformTeamForFrontend(array $result): array
    {
        //remove accepted invites
        $invitations = $this->removeAcceptedInvites($result['invitations']);
        $users = array_merge($result['memberships'], $invitations);

        return [
            'teamId' => $result['id'],
            'users' => $users,
            'originalData' => $result,
        ];
    }

    private function removeAcceptedInvites(array $invitations): array
    {
        foreach ($invitations as $key => $invite) {
            if ($invite['accepted']) {
                unset($invitations[$key]);
            }
        }

        return $invitations;
    }
}
