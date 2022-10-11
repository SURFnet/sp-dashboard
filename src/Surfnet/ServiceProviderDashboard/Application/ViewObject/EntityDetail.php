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

namespace Surfnet\ServiceProviderDashboard\Application\ViewObject;

use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Attribute;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Contact;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class EntityDetail
{
    /**
     * The local storage id
     * @var string
     */
    private $id;

    /**
     * The manage id
     * @var string
     */
    private $manageId;

    /**
     * @var string
     */
    private $metadataUrl;

    /**
     * @var array
     */
    private $acsLocations = [];

    /**
     * @var string
     */
    private $entityId;

    /**
     * @var string
     */
    private $protocol;

    /**
     * @var string
     */
    private $certificate;

    /**
     * @var string
     */
    private $logoUrl;

    /**
     * @var string
     */
    private $nameNl;

    /**
     * @var string
     */
    private $nameEn;

    /**
     * @var string
     */
    private $descriptionNl;

    /**
     * @var string
     */
    private $descriptionEn;

    /**
     * @var string
     */
    private $applicationUrl;

    /**
     * @var string
     */
    private $eulaUrl;

    /**
     * @var Contact
     */
    private $administrativeContact;

    /**
     * @var Contact
     */
    private $technicalContact;

    /**
     * @var Contact
     */
    private $supportContact;

    /**
     * @var EntityDetailAttribute[]
     */
    private $attributes;

    /**
     * @var string
     */
    private $nameIdFormat;

    /**
     * @var string
     */
    private $organizationNameNl;

    /**
     * @var string
     */
    private $organizationNameEn;

    /**
     * @var string
     */
    private $organizationDisplayNameNl;

    /**
     * @var string
     */
    private $organizationDisplayNameEn;

    /**
     * @var string
     */
    private $organizationUrlNl;

    /**
     * @var string
     */
    private $organizationUrlEn;

    /**
     * @var EntityActions
     */
    private $actions;

    /**
     * @var string[]
     */
    private $redirectUris;

    /**
     * @var array
     */
    private $grants;

    /**
     * @var bool
     */
    private $playgroundEnabled;

    /**
     * @var int
     */
    private $accessTokenValidity;

    /**
     * @var bool
     */
    private $isPublicClient;

    /**
     * @var ManageEntity[]|null
     */
    private $resourceServers = null;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        string $id,
        string $manageId,
        string $metadataUrl,
        array $acsLocations,
        string $entityId,
        string $protocol,
        string $certificate,
        ?string $logoUrl,
        string $nameNl,
        string $nameEn,
        string $descriptionNl,
        string $descriptionEn,
        string $applicationUrl,
        string $eulaUrl,
        ?Contact $administrativeContact,
        ?Contact $technicalContact,
        ?Contact $supportContact,
        array $attributes,
        EntityActions $actions,
        string $nameIdFormat,
        string $organizationNameNl,
        string $organizationNameEn,
        string $organizationDisplayNameNl,
        string $organizationDisplayNameEn,
        string $organizationUrlNl,
        string $organizationUrlEn,
        ?array $redirectUris,
        ?array $grants,
        ?bool $playgroundEnabled,
        ?int $accessTokenValidity,
        ?bool $isPublicClient,
        ?array $resourceServers
    ) {
        $this->id = $id;
        $this->manageId = $manageId;
        $this->metadataUrl = $metadataUrl;
        $this->acsLocations = $acsLocations;
        $this->entityId = $entityId;
        $this->protocol = $protocol;
        $this->certificate = $certificate;
        $this->logoUrl = $logoUrl;
        $this->nameNl = $nameNl;
        $this->nameEn = $nameEn;
        $this->descriptionNl = $descriptionNl;
        $this->descriptionEn = $descriptionEn;
        $this->applicationUrl = $applicationUrl;
        $this->eulaUrl = $eulaUrl;
        $this->administrativeContact = $administrativeContact;
        $this->technicalContact = $technicalContact;
        $this->supportContact = $supportContact;
        $this->attributes = $attributes;
        $this->nameIdFormat = $nameIdFormat;
        $this->organizationNameNl = $organizationNameNl;
        $this->organizationNameEn = $organizationNameEn;
        $this->organizationDisplayNameNl = $organizationDisplayNameNl;
        $this->organizationDisplayNameEn = $organizationDisplayNameEn;
        $this->organizationUrlNl = $organizationUrlNl;
        $this->organizationUrlEn = $organizationUrlEn;
        $this->actions = $actions;
        $this->redirectUris = $redirectUris;
        $this->grants = $grants;
        $this->playgroundEnabled = $playgroundEnabled;
        $this->accessTokenValidity = $accessTokenValidity;
        $this->isPublicClient = $isPublicClient;
        $this->resourceServers = $resourceServers;
    }

    public function isLocalEntity()
    {
        return !is_null($this->id);
    }

    /**
     * @return string
     */
    public function getMetadataUrl()
    {
        return $this->metadataUrl;
    }

    /**
     * @return array
     */
    public function getAcsLocations()
    {
        return $this->acsLocations;
    }

    /**
     * @return string
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @return string
     */
    public function getCertificate()
    {
        return $this->certificate;
    }

    /**
     * @return string
     */
    public function getLogoUrl()
    {
        return $this->logoUrl;
    }

    /**
     * @return string
     */
    public function getNameNl()
    {
        return $this->nameNl;
    }

    /**
     * @return string
     */
    public function getNameEn()
    {
        return $this->nameEn;
    }

    /**
     * @return string
     */
    public function getDescriptionNl()
    {
        return $this->descriptionNl;
    }

    /**
     * @return string
     */
    public function getDescriptionEn()
    {
        return $this->descriptionEn;
    }

    /**
     * @return string
     */
    public function getApplicationUrl()
    {
        return $this->applicationUrl;
    }

    /**
     * @return string
     */
    public function getEulaUrl()
    {
        return $this->eulaUrl;
    }

    /**
     * @return Contact
     */
    public function getAdministrativeContact()
    {
        return $this->administrativeContact;
    }

    /**
     * @return Contact
     */
    public function getTechnicalContact()
    {
        return $this->technicalContact;
    }

    /**
     * @return Contact
     */
    public function getSupportContact()
    {
        return $this->supportContact;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @return string
     */
    public function getNameIdFormat()
    {
        return $this->nameIdFormat;
    }

    /**
     * @return string
     */
    public function getOrganizationNameNl()
    {
        return $this->organizationNameNl;
    }

    /**
     * @return string
     */
    public function getOrganizationNameEn()
    {
        return $this->organizationNameEn;
    }

    /**
     * @return string
     */
    public function getOrganizationDisplayNameNl()
    {
        return $this->organizationDisplayNameNl;
    }

    /**
     * @return string
     */
    public function getOrganizationDisplayNameEn()
    {
        return $this->organizationDisplayNameEn;
    }

    /**
     * @return string
     */
    public function getOrganizationUrlNl()
    {
        return $this->organizationUrlNl;
    }

    /**
     * @return string
     */
    public function getOrganizationUrlEn()
    {
        return $this->organizationUrlEn;
    }

    /**
     * @return string
     */
    public function getManageId()
    {
        return $this->manageId;
    }

    /**
     * @return EntityActions
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * @return string
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * @return string[]
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
     * @return bool
     */
    public function isPlaygroundEnabled()
    {
        return $this->playgroundEnabled;
    }

    /**
     * @return int
     */
    public function getAccessTokenValidity()
    {
        return $this->accessTokenValidity;
    }

    /**
     * @return bool
     */
    public function isPublicClient()
    {
        return $this->isPublicClient;
    }

    public function getOrganizationUnitNameAttribute(): ?Attribute
    {
        return $this->organizationUnitNameAttribute;
    }

    /**
     * @return ManageEntity[]|null
     */
    public function getResourceServers(): ?array
    {
        return $this->resourceServers;
    }
}
