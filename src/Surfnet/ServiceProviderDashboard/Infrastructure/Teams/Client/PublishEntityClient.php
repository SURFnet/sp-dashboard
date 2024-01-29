<?php

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
use Psr\Log\LoggerInterface;
use Surfnet\ServiceProviderDashboard\Domain\Repository\PublishTeamsRepository as PublishTeamsRepositoryInterface;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\HttpException\HttpException;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\ChangeMembershipRoleException
    as ChangeMembershipRoleException;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\CreateTeamsException;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\ResendInviteException;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\RuntimeException;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\SendInviteException;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\HttpClient;

class PublishEntityClient implements PublishTeamsRepositoryInterface
{
    public function __construct(
        private readonly HttpClient $client,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @throws CreateTeamsException
     */
    public function createTeam(array $team): mixed
    {
        try {
            $this->logger->info(sprintf('Creating new team \'%s\' in teams', $team['name']));

            $response = $this->client->post(
                json_encode($team),
                '/api/spdashboard/teams'
            );

            if (!isset($response['id'])) {
                throw new CreateTeamsException(
                    sprintf('Unable to create a team for %s in teams', $team['name'])
                );
            }

            return $response;
        } catch (HttpException|GuzzleException|RuntimeException $e) {
            throw new CreateTeamsException(
                sprintf('Unable to create a team for %s in teams', $team['name']),
                0,
                $e
            );
        }
    }

    /**
     * @throws ChangeMembershipRoleException
     */
    public function changeMembership(int $id, string $role): mixed
    {
        try {
            $response = $this->client->put(
                json_encode(
                    [
                    "id" => $id,
                    "role" => strtoupper($role),
                    ]
                ),
                '/api/spdashboard/memberships',
                ['Content-Type' => 'application/json']
            );
        } catch (HttpException|GuzzleException|RuntimeException $e) {
            $this->logger->error(
                'Unable to change membership role',
                $response ?? []
            );
            throw new ChangeMembershipRoleException('Unable to change membership role', 0, $e);
        }

        if ($response->getStatusCode() !== 201) {
            $this->logger->error(
                'Teams rejected the change to the membership role',
                $response ?? []
            );
            throw new ChangeMembershipRoleException('Changing the membership role did not succeed');
        }
        return $response;
    }

    /**
     * @throws SendInviteException
     */
    public function inviteMember(array $inviteObject): mixed
    {
        try {
            // encode inviteObject to JSON, but replace the notation for the emails array from object to array.
            $jsonInviteObject = $this->replaceEmailBrackets(json_encode($inviteObject));

            $response = $this->client->post(
                $jsonInviteObject,
                '/api/spdashboard/invites',
                ['Content-Type' => 'application/json']
            );
        } catch (HttpException|GuzzleException|RuntimeException $e) {
            $this->logger->error(
                'Unable to send the invite',
                $response ?? []
            );
            throw new SendInviteException('Unable to send the invite.', 0, $e);
        }

        if ($response->getStatusCode() !== 201) {
            $this->logger->error(
                'Teams could not send the invite.',
                $response ?? []
            );
            throw new SendInviteException('Unable to send the invite.');
        }
        return $response;
    }

    /**
     * @throws ResendInviteException
     */
    public function resendInvitation(int $id, string $message): mixed
    {
        try {
            $response = $this->client->put(
                json_encode(
                    [
                    "id" => $id,
                    "message" => $message,
                    ]
                ),
                '/api/spdashboard/invites',
                ['Content-Type' => 'application/json']
            );
        } catch (HttpException|GuzzleException|RuntimeException $e) {
            $this->logger->error(
                'Unable to resend the invite',
                $response ?? []
            );
            throw new ResendInviteException('Unable to resend the invite.', 0, $e);
        }

        if ($response->getReasonPhrase() !== "Created") {
            $this->logger->error(
                'Teams could not resend the invite.',
                $response ?? []
            );
            throw new ResendInviteException('Unable to resend the invite.');
        }
        return $response;
    }

    private function replaceEmailBrackets(string $json): string
    {
        $emailPart = substr($json, 1, -1);
        $noCurlyOpeningBrackets = explode('{', $emailPart);
        $noCurlyOpeningBrackets = implode('[', $noCurlyOpeningBrackets);
        $noCurlyClosingBrackets = explode('}', $noCurlyOpeningBrackets);
        $noCurlyClosingBrackets = implode(']', $noCurlyClosingBrackets);

        return substr($json, 0, 1) . $noCurlyClosingBrackets . substr($json, -1, 1);
    }
}
