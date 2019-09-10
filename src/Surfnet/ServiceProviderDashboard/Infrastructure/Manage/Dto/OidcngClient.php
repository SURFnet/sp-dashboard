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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Dto;

use Webmozart\Assert\Assert;

class OidcngClient implements OidcClientInterface
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
    private $redirectUris;
    /**
     * @var string
     */
    private $grantType;
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

    /**
     * @param array $data
     * @param string $manageProtocol
     * @return OidcngClient
     */
    public static function fromApiResponse(array $data, $manageProtocol)
    {
        $clientId = isset($data['data']['entityid']) ? $data['data']['entityid'] : '';
        $clientSecret = isset($data['data']['metaDataFields']['secret']) ? $data['data']['metaDataFields']['secret'] : '';
        $redirectUris = isset($data['data']['metaDataFields']['redirectUrls'])
            ? $data['data']['metaDataFields']['redirectUrls'] : '';
        $grantType = isset($data['data']['metaDataFields']['grants'])
            ? reset($data['data']['metaDataFields']['grants']) : '';
        $scope = isset($data['data']['metaDataFields']['scopes']) ? $data['data']['metaDataFields']['scopes'] : '';
        $isPublicClient = isset($data['data']['metaDataFields']['isPublicClient'])
            ? $data['data']['metaDataFields']['isPublicClient'] : true;
        $accessTokenValidity = isset($data['data']['metaDataFields']['accessTokenValidity'])
            ? $data['data']['metaDataFields']['accessTokenValidity'] : 3600;

        $resourceServers = isset($data['data']['allowedResourceServers']) ? self::parseResourceServers(
            $data['data']['allowedResourceServers']
        ) : [];

        Assert::stringNotEmpty($clientId);
        Assert::string($clientSecret);
        Assert::isArray($redirectUris);
        Assert::string($grantType);
        Assert::isArray($scope);
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
     * @param string $clientId ,
     * @param string $clientSecret
     * @param array $redirectUris
     * @param string $grantType
     * @param array $scope
     * @param bool $isPublicClient
     * @param int $accessTokenValidity
     * @param array $resourceServers
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    private function __construct(
        $clientId,
        $clientSecret,
        $redirectUris,
        $grantType,
        $scope,
        $isPublicClient,
        $accessTokenValidity,
        $resourceServers
    ) {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUris = $redirectUris;
        $this->grantType = $grantType;
        $this->scope = $scope;
        $this->isPublicClient = $isPublicClient;
        $this->accessTokenValidity = $accessTokenValidity;
        $this->resourceServers = $resourceServers;
    }

    private static function parseResourceServers($allowedResourceServers)
    {
        $servers = [];
        foreach ($allowedResourceServers as $clientId) {
            $servers[$clientId['name']] = $clientId['name'];
        }
        return $servers;
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

    /**
     * @return string
     */
    public function getGrantType()
    {
        return $this->grantType;
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
    public function getAccessTokenValidity()
    {
        return $this->accessTokenValidity;
    }

    public function getResourceServers()
    {
        return $this->resourceServers;
    }
}
