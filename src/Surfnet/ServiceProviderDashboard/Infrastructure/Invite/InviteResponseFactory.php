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

use Surfnet\ServiceProviderDashboard\Domain\ValueObject\CreateRoleResponse;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\InviteException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class InviteResponseFactory
{

    public function createFromResponse(ResponseInterface $response, string $name): CreateRoleResponse
    {
        if ($response->getStatusCode() === Response::HTTP_CONFLICT) {
            throw new InviteException(
                sprintf('The name "%s" already exists, please use a unique name.', $name)
            );
        }

        try {
            if ($response->getStatusCode() === Response::HTTP_BAD_REQUEST) {
                throw new InviteException(
                    sprintf('Unable to create role for %s in invite due to a bad request.', $name)
                );
            }
            if ($response->getStatusCode() !== Response::HTTP_CREATED) {
                throw new InviteException(
                    sprintf('Unable to create role for %s in invite. Error code "%s"', $name, $response->getStatusCode())
                );
            }
            if (!$this->isValidCreateResponse($response)) {
                throw new InviteException(
                    sprintf('Unable to create role for %s in invite, invalid response', $name)
                );
            }

            $data = $response->toArray();

            return new CreateRoleResponse($data['id'], $data['name'], $data['shortName'], $data['description'], $data['urn']);
        } catch (TransportExceptionInterface $e) {
            throw new InviteException(
                sprintf('Unable to create role for %s in invite due to a transport error', $name)
            );
        } catch (ClientExceptionInterface|DecodingExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface $e) {
            throw new InviteException(
                sprintf('Unable to create role for %s in invite. Could not parse response.', $name)
            );
        }
    }

    private function isValidCreateResponse(ResponseInterface $response): bool
    {
        $data = $response->toArray();

        if (!isset($data['urn'], $data['id'])) {
            return false;
        }

        if (trim($data['urn']) === '') {
            return false;
        }

        if ($data['id'] < 0) {
            return false;
        }

        return true;
    }
}
