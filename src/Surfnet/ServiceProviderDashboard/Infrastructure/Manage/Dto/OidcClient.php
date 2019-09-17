<?php

/**
 * Copyright 2018 SURFnet B.V.
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

use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Exception\RuntimeException;
use Webmozart\Assert\Assert;

class OidcClient implements OidcClientInterface
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
     * @param array $data
     * @return OidcClient|null
     */
    public static function fromApiResponse(array $data, $manageProtocol)
    {
        if (!isset($data['data']['oidcClient'])) {
            return null;
        }

        $clientId = isset($data['data']['oidcClient']['clientId']) ? $data['data']['oidcClient']['clientId'] : '';
        $clientSecret = isset($data['data']['oidcClient']['clientSecret']) ? $data['data']['oidcClient']['clientSecret'] : '';
        $redirectUris = isset($data['data']['oidcClient']['redirectUris']) ? $data['data']['oidcClient']['redirectUris'] : '';
        $grantType = isset($data['data']['oidcClient']['grantType']) ? $data['data']['oidcClient']['grantType'] : '';
        $scope = isset($data['data']['oidcClient']['scope']) ? $data['data']['oidcClient']['scope'] : '';

        Assert::stringNotEmpty($clientId);
        Assert::string($clientSecret);
        Assert::isArray($redirectUris);
        Assert::string($grantType);
        Assert::isArray($scope);

        return new self(
            $clientId,
            $clientSecret,
            $redirectUris,
            $grantType,
            $scope
        );
    }

    /**
     * @param string $clientId,
     * @param string $clientSecret
     * @param array $redirectUris
     * @param string $grantType
     * @param string $nameIdFormat
     * @param array $scope
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    private function __construct(
        $clientId,
        $clientSecret,
        $redirectUris,
        $grantType,
        $scope
    ) {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUris = $redirectUris;
        $this->grantType = $grantType;
        $this->scope = $scope;
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
     * @throws RuntimeException
     */
    public function isPublicClient()
    {
        throw new RuntimeException('This method is not supported by the OidcClient');
    }

    /**
     * @throws RuntimeException
     */
    public function getAccessTokenValidity()
    {
        throw new RuntimeException('This method is not supported by the OidcClient');
    }

    /**
     * @throws RuntimeException
     */
    public function getResourceServers()
    {
        throw new RuntimeException('This method is not supported by the OidcClient');
    }
}
