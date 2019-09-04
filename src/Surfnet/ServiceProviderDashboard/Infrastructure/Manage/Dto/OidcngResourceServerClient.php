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
    private $grantType;
    /**
     * @var array
     */
    private $scope;
    /**
     * @param array $data
     * @param string $manageProtocol
     * @return OidcngClient
     */
    public static function fromApiResponse(array $data, $manageProtocol)
    {
        $clientId = isset($data['data']['entityid']) ? $data['data']['entityid'] : '';
        $clientSecret = isset($data['data']['metaDataFields']['secret']) ? $data['data']['metaDataFields']['secret'] : '';
        $grantType = isset($data['data']['metaDataFields']['grants'])
            ? reset($data['data']['metaDataFields']['grants']) : '';
        $scope = isset($data['data']['metaDataFields']['scopes']) ? $data['data']['metaDataFields']['scopes'] : '';

        Assert::stringNotEmpty($clientId);
        Assert::string($clientSecret);
        Assert::string($grantType);
        Assert::isArray($scope);

        return new self(
            $clientId,
            $clientSecret,
            $grantType,
            $scope
        );
    }

    /**
     * @param string $clientId ,
     * @param string $clientSecret
     * @param string $grantType
     * @param array $scope
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    private function __construct(
        $clientId,
        $clientSecret,
        $grantType,
        $scope
    ) {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
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
    public function getAccessTokenValidity()
    {
        return 0;
    }
}
