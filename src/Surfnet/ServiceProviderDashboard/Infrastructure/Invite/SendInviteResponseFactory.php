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

use Surfnet\ServiceProviderDashboard\Domain\ValueObject\SendInviteResponse;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\InviteException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class SendInviteResponseFactory
{
    public function createFromSendInviteResponse(ResponseInterface $response, string $name): SendInviteResponse
    {
        try {
            if ($response->getStatusCode() === Response::HTTP_NOT_FOUND) {
                throw new InviteException(
                    sprintf('Unable to send invite. Role "%s" not found in Invite.', $name)
                );
            }

            if ($response->getStatusCode() === Response::HTTP_BAD_REQUEST) {
                throw new InviteException(
                    sprintf('Unable to send invite for "%s". Bad request.', $name)
                );
            }

            if ($response->getStatusCode() !== Response::HTTP_CREATED) {
                throw new InviteException(
                    sprintf('Unable to send invite for %s. Error code "%s"', $name, $response->getStatusCode())
                );
            }
            if (!$this->isValidSendInviteResponse($response)) {
                throw new InviteException(
                    sprintf('Unable to send invite for %s, invalid response', $name)
                );
            }

            $data = $response->toArray();

            return new SendInviteResponse($data['recipientInvitationURLs'][0]['recipient'], $data['recipientInvitationURLs'][0]['invitationURL']);
        } catch (TransportExceptionInterface $e) {
            throw new InviteException(
                sprintf('Unable to send invite for %s due to a transport error', $name)
            );
        } catch (ClientExceptionInterface|DecodingExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface $e) {
            throw new InviteException(
                sprintf('Unable to send invite for %s. Could not parse response.', $name)
            );
        }
    }

    private function isValidSendInviteResponse(ResponseInterface $response): bool
    {
        $data = $response->toArray();

        if (!isset($data['recipientInvitationURLs'][0])) {
            return false;
        }

        $urls = $data['recipientInvitationURLs'][0];

        return is_string($urls['recipient']) && is_string($urls['invitationURL']);
    }
}
