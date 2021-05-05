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

namespace Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;

use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\SecretInterface;
use Webmozart\Assert\Assert;
use function array_filter;
use function array_merge;
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
    /**
     * @var array
     */
    private $resourceServers;

    public static function fromApiResponse(array $data)
    {
        $clientId = isset($data['data']['entityid']) ? $data['data']['entityid'] : '';
        $clientSecret = isset($data['data']['metaDataFields']['secret']) ? $data['data']['metaDataFields']['secret'] : '';
        $resourceServers = isset($data['resourceServers']) ? $data['resourceServers'] : [];
        Assert::stringNotEmpty($clientId);
        Assert::string($clientSecret);
        Assert::isArray($resourceServers);


        return new self(
            $clientId,
            $clientSecret,
            $resourceServers
        );
    }

    public function __construct(
        string $clientId,
        ?string $clientSecret,
        array $resourceServers
    ) {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->resourceServers = $resourceServers;
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

    public function getResourceServers()
    {
        return $this->resourceServers;
    }

    public function resetResourceServers(): void
    {
        $this->resourceServers = [];
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
        $this->mergeResourceServers($client->getResourceServers(), $homeTeam);
    }

    private function mergeResourceServers(array $clientResourceServers, string $homeTeam)
    {
        $manageResourceServers = $this->resourceServers;
        // Filter out the Manage managed RS servers, from outside the 'home' team.
        $manageResourceServers = array_filter($manageResourceServers, function (ManageEntity $server) use ($homeTeam) {
            $teamName = $server->getMetaData()->getCoin()->getServiceTeamId();
            return $homeTeam !== $teamName;
        });
        $manageRsEntityIds = [];
        // Reduce the manage entities to only their entityId
        foreach ($manageResourceServers as $server) {
            $manageRsEntityIds[] = $server->getMetaData()->getEntityId();
        }
        // The combination of the manage specific entityIds and the ones configured on the form is the
        // desired combination.
        $this->resourceServers = array_merge($clientResourceServers, $manageRsEntityIds);
    }
}
