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
     * @param                                          ManageEntity[]|null $resourceServers
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

    /**
     * @return string
     */
    public function getMetadataUrl(): string
    {
        return $this->metadataUrl;
    }

    /**
     * @return array
     */
    public function getAcsLocations(): array
    {
        return $this->acsLocations;
    }

    /**
     * @return string
     */
    public function getEntityId(): string
    {
        return $this->entityId;
    }

    /**
     * @return string
     */
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

    /**
     * @return string
     */
    public function getNameNl(): string
    {
        return $this->nameNl;
    }

    /**
     * @return string
     */
    public function getNameEn(): string
    {
        return $this->nameEn;
    }

    /**
     * @return string
     */
    public function getDescriptionNl(): string
    {
        return $this->descriptionNl;
    }

    /**
     * @return string
     */
    public function getDescriptionEn(): string
    {
        return $this->descriptionEn;
    }

    /**
     * @return string
     */
    public function getApplicationUrl(): string
    {
        return $this->applicationUrl;
    }

    /**
     * @return string
     */
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

    /**
     * @return string
     */
    public function getNameIdFormat(): string
    {
        return $this->nameIdFormat;
    }

    /**
     * @return string
     */
    public function getOrganizationNameNl(): string
    {
        return $this->organizationNameNl;
    }

    /**
     * @return string
     */
    public function getOrganizationNameEn(): string
    {
        return $this->organizationNameEn;
    }

    /**
     * @return string
     */
    public function getOrganizationDisplayNameNl(): string
    {
        return $this->organizationDisplayNameNl;
    }

    /**
     * @return string
     */
    public function getOrganizationDisplayNameEn(): string
    {
        return $this->organizationDisplayNameEn;
    }

    /**
     * @return string
     */
    public function getOrganizationUrlNl(): string
    {
        return $this->organizationUrlNl;
    }

    /**
     * @return string
     */
    public function getOrganizationUrlEn(): string
    {
        return $this->organizationUrlEn;
    }

    /**
     * @return string
     */
    public function getManageId(): string
    {
        return $this->manageId;
    }

    /**
     * @return EntityActions
     */
    public function getActions(): EntityActions
    {
        return $this->actions;
    }

    /**
     * @return string
     */
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
}
