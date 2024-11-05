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

use DateTimeImmutable;
use DateTimeInterface;
use Psr\Log\LoggerInterface;
use \Surfnet\ServiceProviderDashboard\Domain\Repository\Invite\SendInviteRepository as SendInviteRepositoryInterface;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\SendInviteResponse;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\InviteException;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\RuntimeException;

readonly class SendInviteRepository implements SendInviteRepositoryInterface
{
    public function __construct(
        private InviteHttpClient $client,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @throws InviteException
     */
    public function sendInvite(
        string $email,
        string $message,
        string $language,
        int $roleIdentifier,
    ): SendInviteResponse {
        $expiryDate = new DateTimeImmutable("+14 days");

        $team = $this->createSendInvitePayload(
            $email,
            $message,
            $language,
            $roleIdentifier,
            $expiryDate,
        );

        try {
            $this->logger->info(sprintf('Creating new role \'%s\' in invite', $email));

            $response = $this->client->post('/internal/invite/invitations', $team);
            return (new SendInviteResponseFactory())->createFromSendInviteResponse($response, $email);
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
     * @return array<string,mixed>
     */
    private function createSendInvitePayload(
        string $email,
        string $message,
        string $language,
        int $roleIdentifier,
        DateTimeInterface $expiryDate,
    ): array {

        return [
            'intendedAuthority' => 'INVITER',
            'message' => $message,
            'language' => $language,
            'guestRoleIncluded' => true,
            'invites' => [
                $email,
            ],
            'roleIdentifiers' => [
                $roleIdentifier,
            ],
            'expiryDate' => $expiryDate->getTimestamp(),
            ];
    }
}
