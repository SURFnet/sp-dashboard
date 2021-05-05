<?php

/**
 * Copyright 2019 SURFnet B.V.
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

use Surfnet\ServiceProviderDashboard\Domain\ValueObject\SecretInterface;
use Webmozart\Assert\Assert;
use function is_null;

class OidcngResourceServerClient implements OidcClientInterface
{
    /**
     * @var string
     */
    private $clientId;
    /**
     * @var string
     */
    private $clientSecret;
    /**
     * @var string
     */
    private $grants;

    public static function fromApiResponse(array $data)
    {
        $clientId = isset($data['data']['entityid']) ? $data['data']['entityid'] : '';
        $clientSecret = isset($data['data']['metaDataFields']['secret']) ? $data['data']['metaDataFields']['secret'] : '';
        $grants = isset($data['data']['metaDataFields']['grants'])
            ? $data['data']['metaDataFields']['grants'] : [];

        Assert::stringNotEmpty($clientId);
        Assert::string($clientSecret);
        Assert::isArray($grants);

        return new self(
            $clientId,
            $clientSecret,
            $grants
        );
    }

    public function __construct(
        string $clientId,
        ?string $clientSecret,
        array $grants
    ) {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->grants = $grants;
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

    public function getGrants(): array
    {
        return $this->grants;
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

    public function updateClientSecret(SecretInterface $secret): void
    {
        $this->clientSecret = $secret->getSecret();
    }

    public function merge(OidcClientInterface $client, string $homeTeam): void
    {
        $this->clientId = is_null($client->getClientId()) ? null : $client->getClientId();
        $this->clientSecret = is_null($client->getClientSecret()) ? null : $client->getClientSecret();
        $this->grants = is_null($client->getGrants()) ? null : $client->getGrants();
    }
}
