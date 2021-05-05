<?php

/**
 * Copyright 2021 SURF B.V.
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

namespace Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;

use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\SecretInterface;
use Webmozart\Assert\Assert;
use function is_null;

class OauthClientCredentialsClientClient implements OidcClientInterface
{
    /**
     * @var string
     */
    private $clientId;
    /**
     * @var string
     */
    private $clientSecret;

    public static function fromApiResponse(array $data)
    {
        $clientId = isset($data['data']['entityid']) ? $data['data']['entityid'] : '';
        $clientSecret = isset($data['data']['metaDataFields']['secret']) ? $data['data']['metaDataFields']['secret'] : '';
        Assert::stringNotEmpty($clientId);
        Assert::string($clientSecret);

        return new self(
            $clientId,
            $clientSecret
        );
    }

    public function __construct(
        string $clientId,
        ?string $clientSecret
    ) {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @return string
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    /**
     * @return array
     */
    public function getRedirectUris()
    {
        return [];
    }

    /**
     * @return bool
     */
    public function isPublicClient()
    {
        return false;
    }

    /**
     * @return int
     */
    public function getAccessTokenValidity(): int
    {
        return 0;
    }

    /**
     * @return array
     */
    public function getResourceServers()
    {
        return [];
    }

    public function resetResourceServers(): void
    {
        // Nothing to do here.
    }

    public function getGrants(): array
    {
        return [Constants::GRANT_TYPE_CLIENT_CREDENTIALS];
    }

    public function updateClientSecret(SecretInterface $secret): void
    {
        $this->clientSecret = $secret->getSecret();
    }

    public function merge(OidcClientInterface $client, string $homeTeam): void
    {
        $this->clientId = is_null($client->getClientId()) ? null : $client->getClientId();
        $this->clientSecret = is_null($client->getClientSecret()) ? null : $client->getClientSecret();
    }
}
