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
     * @var OidcClient
     */
    private $oidcClient;

    public static function fromApiResponse($data)
    {
        $attributeList = AttributeList::fromApiResponse($data);
        $metaData = MetaData::fromApiResponse($data);
        $oidcClient = OidcClient::fromApiResponse($data);
        return new self($data['id'], $attributeList, $metaData, $oidcClient);
    }

    /**
     * @param string $id
     * @param AttributeList $attributes
     * @param MetaData $metaData
     * @param OidcClient $oidcClient
     */
    private function __construct($id, AttributeList $attributes, MetaData $metaData, OidcClient $oidcClient = null)
    {
        $this->id = $id;
        $this->status = DomainEntity::STATE_PUBLISHED;
        $this->attributes = $attributes;
        $this->metaData = $metaData;
        $this->oidcClient = $oidcClient;
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

    public function getEntityId(){
        if ($this->getProtocol() == DomainEntity::TYPE_OPENID_CONNECT) {
            return $this->getOidcClient()->getClientId();
        }
        return $this->getMetaData()->getEntityId();
    }

    /**
     * @return OidcClient|null
     */
    public function getOidcClient()
    {
        return $this->oidcClient;
    }

    /**
     * @return string
     */
    public function getProtocol()
    {
        if ($this->getMetaData()->getCoin()->getOidcClient()) {
            return DomainEntity::TYPE_OPENID_CONNECT;
        }
        return DomainEntity::TYPE_SAML;
    }
}
