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

namespace Surfnet\ServiceProviderDashboard\Domain\Entity;

use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\AllowedIdentityProviders;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\AttributeList;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\MetaData;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\OauthClientCredentialsClientClient;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\OidcClientInterface;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\OidcngClient;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\OidcngResourceServerClient;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Protocol;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\SecretInterface;
use function in_array;

/**
 * TODO: All factory logic should be offloaded to Application or Infra layers where the
 * entity is used in a specific context. This particularly applies for the factory
 * methods found in the 'Entity/Entity' namespace.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity) This object contains a lot of properties which cannot not be easily
 * simplified.
 */
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
    
    private $comments;

    private $environment;

    /**
     * @var Service
     */
    private $service;
    /**
     * @var bool
     */
    private $readOnly = false;

    /**
     * @param $data
     * @return ManageEntity
     *
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public static function fromApiResponse($data)
    {
        $manageProtocol = self::extractManageProtocol($data);

        $attributeList = AttributeList::fromApiResponse($data);
        $metaData = MetaData::fromApiResponse($data);
        $oidcClient = null;

        switch ($manageProtocol) {
            case Protocol::OAUTH20_RS:
                $oidcClient = OidcngResourceServerClient::fromApiResponse($data);
                break;
            case Protocol::OIDC10_RP:
                $oidcClient = OidcngClient::fromApiResponse($data);
                break;
            case Constants::TYPE_OAUTH_CLIENT_CREDENTIAL_CLIENT:
                $oidcClient = OauthClientCredentialsClientClient::fromApiResponse($data);
                break;
        }

        $allowedEdentityProviders = AllowedIdentityProviders::fromApiResponse($data);
        $protocol = Protocol::fromApiResponse($manageProtocol);

        return new self($data['id'], $attributeList, $metaData, $allowedEdentityProviders, $protocol, $oidcClient);
    }

    public function __construct(
        ?string $id,
        AttributeList $attributes,
        MetaData $metaData,
        AllowedIdentityProviders $allowedIdentityProviders,
        Protocol $protocol,
        ?OidcClientInterface $oidcClient = null,
        ?Service $service = null
    ) {
        $this->id = $id;
        $this->status = Constants::STATE_PUBLISHED;
        $this->attributes = $attributes;
        $this->metaData = $metaData;
        $this->oidcClient = $oidcClient;
        $this->protocol = $protocol;
        $this->allowedIdentityProviders = $allowedIdentityProviders;
        $this->service = $service;
    }

    private static function extractManageProtocol(array $data): string
    {
        $manageProtocol = $data['type'] ?? '';
        $grantTypes = $data['data']['metaDataFields']['grants'] ?? [];
        // No dedicated Manage protocol / entity type is defined yet for oauth client credential entities
        $isClientCredentialsClient = in_array(Constants::GRANT_TYPE_CLIENT_CREDENTIALS, $grantTypes)
            && count($grantTypes) === 1;
        return $isClientCredentialsClient ? Constants::TYPE_OAUTH_CLIENT_CREDENTIAL_CLIENT : $manageProtocol;
    }

    public function resetId()
    {
        $clone = clone $this;
        $clone->id = null;
        return $clone;
    }

    public function setId(string $id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getMetaData(): ?MetaData
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

    public function isPublished()
    {
        return ($this->status === Constants::STATE_PUBLISHED);
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

    public function isExcludedFromPushSet()
    {
        if (is_null($this->getMetaData()->getCoin()->getExcludeFromPush())) {
            return false;
        }
        return true;
    }

    public function isExcludedFromPush()
    {
        if (is_null($this->getMetaData()->getCoin()->getExcludeFromPush())) {
            return false;
        }
        return $this->getMetaData()->getCoin()->getExcludeFromPush() == 1 ? true : false;
    }

    public function isManageEntity(): bool
    {
        return !is_null($this->getId());
    }

    public function setComments(?string $comments): void
    {
        $this->comments = $comments;
    }

    /**
     * @return string
     */
    public function getComments(): ?string
    {
        return $this->comments;
    }

    /**
     * @return bool
     */
    public function hasComments(): bool
    {
        return !(empty($this->comments));
    }

    public function setEnvironment(string $environment): void
    {
        $this->environment = $environment;
    }

    public function getEnvironment(): ?string
    {
        return $this->environment;
    }

    public function isProduction()
    {
        return $this->getEnvironment() === Constants::ENVIRONMENT_PRODUCTION;
    }

    public function setIsReadOnly()
    {
        $this->readOnly = true;
    }

    public function isReadOnly(): bool
    {
        // Entities from outside the current team can be read only (can happen in RP -> RS connections created in Manage)
        return $this->readOnly;
    }

    public function getService(): Service
    {
        return $this->service;
    }

    public function setService(Service $service)
    {
        $this->service = $service;
    }

    /**
     * Merge new data into an existing ManageEntity.
     * @param ManageEntity $newEntity
     */
    public function merge(ManageEntity $newEntity)
    {
        $this->service = is_null($newEntity->getService()) ? null : $newEntity->getService();
        $this->metaData->merge($newEntity->getMetaData());
        $this->attributes->merge($newEntity->getAttributes());
        if ($this->hasOicdClient()) {
            $this->oidcClient->merge($newEntity->getOidcClient(), $this->getService()->getTeamName());
        }
        $this->comments = $newEntity->getComments();
    }

    public function setStatus(string $status)
    {
        $this->status = $status;
    }

    public function updateClientSecret(SecretInterface $secret)
    {
        $this->getOidcClient()->updateClientSecret($secret);
    }

    private function hasOicdClient()
    {
        $protocol = $this->protocol->getProtocol();
        return $protocol === Constants::TYPE_OPENID_CONNECT_TNG
            || $protocol === Constants::TYPE_OPENID_CONNECT_TNG_RESOURCE_SERVER
            || $protocol === Constants::TYPE_OAUTH_CLIENT_CREDENTIAL_CLIENT;
    }

    public function diff(ManageEntity $compareTo): EntityDiff
    {
        $data = $this->asArray();
        $otherData = $compareTo->asArray();
        return new EntityDiff($otherData, $data);
    }

    public function asArray(): array
    {
        $data = [];
        $data['id'] = $this->id;
        $data['revisionnote'] = $this->getComments();
        $data['environment'] = $this->environment;
        $data = $this->metaData->asArray();
        $data += $this->attributes->asArray();
        if ($this->hasOicdClient()) {
            $data += $this->oidcClient->asArray();
        }
        $data += $this->protocol->asArray();
        return $data;
    }

    private function isStatusPublicationRequested()
    {
        return $this->getStatus() === Constants::STATE_PUBLICATION_REQUESTED;
    }

    public function isRequestedProductionEntity(
        bool $isCopy
    ) {
        return $isCopy || ($this->isStatusPublicationRequested() && $this->isProduction());
    }
}
