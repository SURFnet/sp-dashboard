<?php

//declare(strict_types = 1);

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

use Surfnet\ServiceProviderDashboard\Domain\Entity\Comparable;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\SecretInterface;
use Webmozart\Assert\Assert;
use function array_diff;
use function array_filter;
use function array_merge;
use function is_null;
use function strtolower;

class OidcngClient implements Comparable, OidcClientInterface
{
    final public const FORM_MANAGED_GRANTS = [
        'entity.edit.label.authorization_code' => Constants::GRANT_TYPE_AUTHORIZATION_CODE,
        'entity.edit.label.implicit' => Constants::GRANT_TYPE_IMPLICIT,
    ];

    public static function fromApiResponse(array $data): self
    {
        $clientId = self::getLowercasedStringOrEmpty($data['data'], 'entityid');
        $clientSecret = self::getStringOrEmpty($data['data']['metaDataFields'], 'secret');
        $redirectUris = self::getLowercasedArrayOrEmpty($data['data']['metaDataFields'], 'redirectUrls');

        $grantType = $data['data']['metaDataFields']['grants'] ?? [];
        $isPublicClient = $data['data']['metaDataFields']['isPublicClient'] ?? true;
        $accessTokenValidity = $data['data']['metaDataFields']['accessTokenValidity'] ?? 3600;
        $resourceServers = $data['resourceServers'] ?? [];

        Assert::stringNotEmpty($clientId);
        Assert::string($clientSecret);
        Assert::isArray($redirectUris);
        Assert::isArray($grantType);
        Assert::boolean($isPublicClient);
        Assert::numeric($accessTokenValidity);
        Assert::isArray($resourceServers);

        return new self(
            $clientId,
            $clientSecret,
            $redirectUris,
            $grantType,
            $isPublicClient,
            $accessTokenValidity,
            $resourceServers
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        private string $clientId,
        private string $clientSecret,
        private array $redirectUris,
        private array $grants,
        private bool $isPublicClient,
        private int $accessTokenValidity,
        private array $resourceServers,
    ) {
    }

    /**
     * @param  $key
     * @return string
     */
    private static function getStringOrEmpty(array $data, $key)
    {
        return $data[$key] ?? '';
    }

    /**
     * @param  $key
     * @return string
     */
    private static function getLowercasedStringOrEmpty(array $data, $key): string
    {
        return isset($data[$key]) ? strtolower((string) $data[$key]) : '';
    }

    /**
     * @param  $key
     * @return array
     */
    private static function getLowercasedArrayOrEmpty(array $data, $key): array
    {
        $urls = [];
        if (isset($data[$key])) {
            foreach ($data[$key] as $url) {
                $protocolSlashes = strpos((string) $url, '://');
                $hostname = strpos((string) $url, '/', $protocolSlashes + 3);
                $lowercased = strtolower(substr((string) $url, 0, $hostname));
                $urls[] = $lowercased . substr((string) $url, $hostname);
            }
        }
        return $urls;
    }

     /**
      * @return string
      */
    public function getClientId(): string
    {
        return $this->clientId;
    }

    /**
     * @return string
     */
    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    /**
     * @return array
     */
    public function getRedirectUris(): array
    {
        return $this->redirectUris;
    }

    public function getGrants(): array
    {
        return $this->grants;
    }

    /**
     * @return bool
     */
    public function isPublicClient(): bool
    {
        return $this->isPublicClient;
    }

    /**
     * @return int
     */
    public function getAccessTokenValidity(): int
    {
        return $this->accessTokenValidity;
    }

    public function getResourceServers(): array
    {
        return $this->resourceServers;
    }

    public function resetResourceServers(): void
    {
        $this->resourceServers = [];
    }

    public function updateClientSecret(SecretInterface $secret): void
    {
        $this->clientSecret = $secret->getSecret();
    }

    /**
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function merge(OidcClientInterface $client, string $homeTeam): void
    {
        $this->clientId = is_null($client->getClientId()) ? null : $client->getClientId();
        $this->clientSecret = is_null($client->getClientSecret()) ? null : $client->getClientSecret();
        $this->redirectUris = is_null($client->getRedirectUris()) ? null : $client->getRedirectUris();
        $this->isPublicClient = is_null($client->isPublicClient()) ? null : $client->isPublicClient();
        $this->accessTokenValidity = is_null($client->getAccessTokenValidity()) ? null : $client->getAccessTokenValidity();
        $this->mergeGrants($client->getGrants());
        $this->mergeResourceServers($client->getResourceServers(), $homeTeam);
    }

    public function asArray(): array
    {
        return [
            'metaDataFields.accessTokenValidity' => $this->getAccessTokenValidity(),
            'metaDataFields.grants' => $this->getGrants(),
            'metaDataFields.isPublicClient' => $this->isPublicClient(),
            'metaDataFields.redirectUrls' => $this->getRedirectUris(),
            'metaDataFields.secret' => $this->getClientSecret(),
            'entityid' => $this->getClientId(),
        ];
    }

    /**
     * The team name is used to distinguish between Manage selected RS's (possibly outside of own team)
     * and the ones configured in SPD.
     */
    private function mergeResourceServers(array $clientResourceServers, string $homeTeam): void
    {
        $manageResourceServers = $this->resourceServers;
        // Filter out the Manage managed RS servers, from outside the 'home' team.
        $manageResourceServers = array_filter(
            $manageResourceServers,
            function (ManageEntity $server) use ($homeTeam): bool {
                $teamName = $server->getMetaData()->getCoin()->getServiceTeamId();
                return $homeTeam !== $teamName;
            }
        );
        $manageRsEntityIds = [];
        // Reduce the manage entities to only their entityId
        foreach ($manageResourceServers as $server) {
            $manageRsEntityIds[] = $server->getMetaData()->getEntityId();
        }
        // The combination of the manage specific entityIds and the ones configured on the form is the
        // desired combination.
        $this->resourceServers = array_merge($clientResourceServers, $manageRsEntityIds);
    }

    /**
     * Remove the form managed grants from the Manage grants, and reset them with the grants selected on the form
     */
    private function mergeGrants(array $formGrants): void
    {
        $manageGrants = $this->grants;
        $manageGrants = array_diff($manageGrants, self::FORM_MANAGED_GRANTS);
        $this->grants = array_merge($manageGrants, $formGrants);
    }
}
