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
    public $organizationUnitNameAttribute;
    /**
     * @param ManageEntity[]|null $resourceServers
     * @param array<string>|null $typeOfService
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        private readonly string $id,
        private readonly string $manageId,
        private readonly string $metadataUrl,
        private readonly array $acsLocations,
        private readonly string $entityId,
        private readonly string $protocol,
        private readonly string $certificate,
        private readonly ?string $logoUrl,
        private readonly string $nameNl,
        private readonly string $nameEn,
        private readonly string $descriptionNl,
        private readonly string $descriptionEn,
        private readonly string $applicationUrl,
        private readonly string $eulaUrl,
        private readonly ?Contact $administrativeContact,
        private readonly ?Contact $technicalContact,
        private readonly ?Contact $supportContact,
        private readonly array $attributes,
        private readonly EntityActions $actions,
        private readonly string $nameIdFormat,
        private readonly string $organizationNameNl,
        private readonly string $organizationNameEn,
        private readonly string $organizationDisplayNameNl,
        private readonly string $organizationDisplayNameEn,
        private readonly string $organizationUrlNl,
        private readonly string $organizationUrlEn,
        private readonly ?array $redirectUris,
        private readonly ?array $grants,
        private readonly ?bool $playgroundEnabled,
        private readonly ?int $accessTokenValidity,
        private readonly ?bool $isPublicClient,
        private readonly ?array $resourceServers,
    ) {
    }

    public function isLocalEntity(): bool
    {
        return !is_null($this->id);
    }

    public function getMetadataUrl(): string
    {
        return $this->metadataUrl;
    }

    public function getAcsLocations(): array
    {
        return $this->acsLocations;
    }

    public function getEntityId(): string
    {
        return $this->entityId;
    }

    public function getCertificate(): string
    {
        return $this->certificate;
    }

    /**
     * @return string
     */
    public function getLogoUrl(): ?string
    {
        return $this->logoUrl;
    }

    public function getNameNl(): string
    {
        return $this->nameNl;
    }

    public function getNameEn(): string
    {
        return $this->nameEn;
    }

    public function getDescriptionNl(): string
    {
        return $this->descriptionNl;
    }

    public function getDescriptionEn(): string
    {
        return $this->descriptionEn;
    }

    public function getApplicationUrl(): string
    {
        return $this->applicationUrl;
    }

    public function getEulaUrl(): string
    {
        return $this->eulaUrl;
    }

    /**
     * @return Contact
     */
    public function getAdministrativeContact(): ?Contact
    {
        return $this->administrativeContact;
    }

    /**
     * @return Contact
     */
    public function getTechnicalContact(): ?Contact
    {
        return $this->technicalContact;
    }

    /**
     * @return Contact
     */
    public function getSupportContact(): ?Contact
    {
        return $this->supportContact;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getNameIdFormat(): string
    {
        return $this->nameIdFormat;
    }

    public function getOrganizationNameNl(): string
    {
        return $this->organizationNameNl;
    }

    public function getOrganizationNameEn(): string
    {
        return $this->organizationNameEn;
    }

    public function getOrganizationDisplayNameNl(): string
    {
        return $this->organizationDisplayNameNl;
    }

    public function getOrganizationDisplayNameEn(): string
    {
        return $this->organizationDisplayNameEn;
    }

    public function getOrganizationUrlNl(): string
    {
        return $this->organizationUrlNl;
    }

    public function getOrganizationUrlEn(): string
    {
        return $this->organizationUrlEn;
    }

    public function getManageId(): string
    {
        return $this->manageId;
    }

    public function getActions(): EntityActions
    {
        return $this->actions;
    }

    public function getProtocol(): string
    {
        return $this->protocol;
    }

    /**
     * @return string[]
     */
    public function getRedirectUris(): ?array
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
    public function isPlaygroundEnabled(): ?bool
    {
        return $this->playgroundEnabled;
    }

    /**
     * @return int
     */
    public function getAccessTokenValidity(): ?int
    {
        return $this->accessTokenValidity;
    }

    /**
     * @return bool
     */
    public function isPublicClient(): ?bool
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

    /**
     * @return array<string>|null
     */
    public function typeOfService(): ?array
    {
        return $this->typeOfService;
    }
}
