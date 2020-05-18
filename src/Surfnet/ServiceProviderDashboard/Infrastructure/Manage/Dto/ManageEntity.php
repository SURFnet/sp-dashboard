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

use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity as DomainEntity;

class ManageEntity
{
    private $id;

    /**
     * @var string
     */
    private $status;

    /**
     * @var AttributeList
     */
    private $attributes;

    /**
     * @var MetaData
     */
    private $metaData;

    /**
     * @var OidcClientInterface
     */
    private $oidcClient;

    /**
     * @var Protocol
     */
    private $protocol;

    /**
     * @var AllowedIdentityProviders
     */
    private $allowedIdentityProviders;

    /**
     * @param $data
     * @return ManageEntity
     *
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public static function fromApiResponse($data)
    {
        $manageProtocol = isset($data['type']) ? $data['type'] : '';

        $attributeList = AttributeList::fromApiResponse($data);
        $metaData = MetaData::fromApiResponse($data);
        if ($manageProtocol === Protocol::OIDC10_RP) {
            if (isset($data['data']['metaDataFields']['isResourceServer']) &&
                $data['data']['metaDataFields']['isResourceServer']
            ) {
                $oidcClient = OidcngResourceServerClient::fromApiResponse($data, $manageProtocol);
            } else {
                $oidcClient = OidcngClient::fromApiResponse($data, $manageProtocol);
            }
        } elseif ($manageProtocol === Protocol::SAML20_SP) {
            // Try to create an OidcClient, the first oidc implementation used SAML20_SP as entity type.
            $oidcClient = OidcClient::fromApiResponse($data, $manageProtocol);
        }
        $allowedEdentityProviders = AllowedIdentityProviders::fromApiResponse($data);
        $protocol = Protocol::fromApiResponse($data, $manageProtocol);

        return new self($data['id'], $attributeList, $metaData, $allowedEdentityProviders, $protocol, $oidcClient);
    }

    /**
     * @param string $id
     * @param AttributeList $attributes
     * @param MetaData $metaData
     * @param AllowedIdentityProviders $allowedIdentityProviders
     * @param Protocol $protocol
     * @param OidcClientInterface $oidcClient
     */
    private function __construct(
        $id,
        AttributeList $attributes,
        MetaData $metaData,
        AllowedIdentityProviders $allowedIdentityProviders,
        Protocol $protocol,
        OidcClientInterface $oidcClient = null
    ) {
        $this->id = $id;
        $this->status = DomainEntity::STATE_PUBLISHED;
        $this->attributes = $attributes;
        $this->metaData = $metaData;
        $this->oidcClient = $oidcClient;
        $this->protocol = $protocol;
        $this->allowedIdentityProviders = $allowedIdentityProviders;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getMetaData()
    {
        return $this->metaData;
    }

    public function updateStatus($newStatus)
    {
        $this->status = $newStatus;
    }

    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return OidcClientInterface|null
     */
    public function getOidcClient()
    {
        return $this->oidcClient;
    }

    /**
     * @return AllowedIdentityProviders
     */
    public function getAllowedIdentityProviders()
    {
        return $this->allowedIdentityProviders;
    }

    /**
     * @return Protocol
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    public function isOidcngResourceServer()
    {
        if ($this->getOidcClient()) {
            return $this->getOidcClient() instanceof OidcngResourceServerClient;
        }

        return false;
    }

    public function isExcludedFromPush()
    {
        if (is_null($this->getMetaData()->getCoin()->getExcludeFromPush())) {
            return true;
        }
        return $this->getMetaData()->getCoin()->getExcludeFromPush() == 1 ? true : false;
    }
}
