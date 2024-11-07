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

use SensitiveParameter;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class InviteHttpClient
{

    private HttpClientInterface $httpClient;

    public function __construct(
        string $host,
        string $path,
        string $username,
        #[SensitiveParameter]  string $password,
    ) {
        $options = [
            'auth_basic' => [$username, $password],

            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ];

        $this->httpClient = HttpClient::createForBaseUri(
            $host . $path,
            $options
        );
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function post(string $path, array $payload): ResponseInterface
    {
        return $this->httpClient->request(
            'POST',
            $path,
            ['json' => $payload],
        );
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function delete(string $path): ResponseInterface
    {
        return $this->httpClient->request(
            'DELETE',
            $path,
        );
    }
}
