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

use Surfnet\ServiceProviderDashboard\Domain\ValueObject\OidcGrantType;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\SecretInterface;
use Webmozart\Assert\Assert;
use function array_diff;
use function array_intersect;
use function is_null;

class OidcngClient implements OidcClientInterface
{
    const FORM_MANAGED_GRANTS = [
        'entity.edit.label.authorization_code' => OidcGrantType::GRANT_TYPE_AUTHORIZATION_CODE,
        'entity.edit.label.implicit' => OidcGrantType::GRANT_TYPE_IMPLICIT
    ];

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
    private $redirectUris;
    /**
     * @var array
     */
    private $grants;
    /**
     * @var array
     */
    private $scope;
    /**
     * @var bool
     */
    private $isPublicClient;
    /**
     * @var int
     */
    private $accessTokenValidity;
    /**
     * @var array
     */
    private $resourceServers;

    public static function fromApiResponse(array $data, string $manageProtocol)
    {
        $clientId = self::getStringOrEmpty($data['data'], 'entityid');
        $clientSecret = self::getStringOrEmpty($data['data']['metaDataFields'], 'secret');
        $redirectUris = self::getArrayOrEmpty($data['data']['metaDataFields'], 'redirectUrls');
        $scope = self::getStringOrNull($data['data']['metaDataFields'], 'scopes');

        $grantType = isset($data['data']['metaDataFields']['grants'])
            ? $data['data']['metaDataFields']['grants'] : [];
        $isPublicClient = isset($data['data']['metaDataFields']['isPublicClient'])
            ? $data['data']['metaDataFields']['isPublicClient'] : true;
        $accessTokenValidity = isset($data['data']['metaDataFields']['accessTokenValidity'])
            ? $data['data']['metaDataFields']['accessTokenValidity'] : 3600;
        $resourceServers = isset($data['resourceServers']) ? $data['resourceServers'] : [];

        Assert::stringNotEmpty($clientId);
        Assert::string($clientSecret);
        Assert::isArray($redirectUris);
        Assert::isArray($grantType);
        Assert::nullOrIsArray($scope);
        Assert::boolean($isPublicClient);
        Assert::numeric($accessTokenValidity);
        Assert::isArray($resourceServers);

        return new self(
            $clientId,
            $clientSecret,
            $redirectUris,
            $grantType,
            $scope,
            $isPublicClient,
            $accessTokenValidity,
            $resourceServers
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        string $clientId,
        string $clientSecret,
        array $redirectUris,
        array $grants,
        ?array $scope,
        bool $isPublicClient,
        int $accessTokenValidity,
        array $resourceServers
    ) {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUris = $redirectUris;
        $this->grants = $grants;
        $this->scope = $scope;
        $this->isPublicClient = $isPublicClient;
        $this->accessTokenValidity = $accessTokenValidity;
        $this->resourceServers = $resourceServers;
    }

    /**
     * @param array $data
     * @param $key
     * @return string
     */
    private static function getStringOrEmpty(array $data, $key)
    {
        return isset($data[$key]) ? $data[$key] : '';
    }

    /**
     * @param array $data
     * @param $key
     * @return array
     */
    private static function getArrayOrEmpty(array $data, $key)
    {
        return isset($data[$key]) ? $data[$key] : [];
    }

    /**
     * @param array $data
     * @param $key
     * @return string|null
     */
    private static function getStringOrNull(array $data, $key)
    {
        return isset($data[$key]) ? $data[$key] : null;
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
        return $this->redirectUris;
    }

    public function getGrants(): array
    {
        return $this->grants;
    }

    /**
     * @return array
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @return bool
     */
    public function isPublicClient()
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

    public function getResourceServers()
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
    public function merge(OidcClientInterface $client): void
    {
        $this->clientId = is_null($client->getClientId()) ? null : $client->getClientId();
        $this->clientSecret = is_null($client->getClientSecret()) ? null : $client->getClientSecret();
        $this->redirectUris = is_null($client->getRedirectUris()) ? null : $client->getRedirectUris();
        $this->mergeGrants($client->getGrants());
        $this->scope = is_null($client->getScope()) ? null : $client->getScope();
        $this->isPublicClient = is_null($client->isPublicClient()) ? null : $client->isPublicClient();
        $this->accessTokenValidity = is_null($client->getAccessTokenValidity()) ? null : $client->getAccessTokenValidity();
        $this->resourceServers = is_null($client->getResourceServers()) ? null : $client->getResourceServers();
    }

    /**
     * Remove the form managed grants from the Manage grants, and reset them with the grants selected on the form
     * @param array $formGrants
     */
    private function mergeGrants(array $formGrants)
    {
        $manageGrants = $this->grants;
        $manageGrants = array_diff($manageGrants, self::FORM_MANAGED_GRANTS);
        $this->grants = array_merge($manageGrants, $formGrants);
    }
}
