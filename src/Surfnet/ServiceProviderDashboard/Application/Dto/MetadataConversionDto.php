<?php

/**
 * Copyright 2020 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Application\Dto;

use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\IdentityProvider;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Contact as ContactPerson;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\OidcGrantType;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\ResourceServerCollection;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class MetadataConversionDto
{
    /**
     * @var Entity
     */
    private $entity;

    /**
     * @var ManageEntity
     */
    private $manageEntity = null;

    private function __construct(Entity $entity, ManageEntity $manageEntity = null)
    {
        $this->entity = $entity;
        $this->manageEntity = $manageEntity;
    }

    /**
     * When used for creation, no Mnaage entity is present yet to create the Dto from
     * @param Entity $entity
     */
    public static function fromEntity(Entity $entity)
    {
        return new self($entity);
    }

    public static function fromManageEntity(ManageEntity $manageEntity, Entity $entity)
    {
        return new self($entity, $manageEntity);
    }

    /**
     * @return Service
     */
    public function getService()
    {
        return $this->entity->getService();
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->entity->getId();
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        return $this->entity->getEnvironment();
    }

    /**
     * @return bool
     */
    public function isArchived()
    {
        return $this->entity->isArchived();
    }

    /**
     * @return string
     */
    public function getManageId()
    {
        return $this->entity->getManageId();
    }

    /**
     * @return string
     */
    public function getImportUrl()
    {
        return $this->entity->getImportUrl();
    }

    /**
     * @return string
     */
    public function getMetadataUrl()
    {
        return $this->entity->getMetadataUrl();
    }

    /**
     * @return string
     */
    public function getAcsLocation()
    {
        return $this->entity->getAcsLocation();
    }

    /**
     * @return string
     */
    public function getClientSecret()
    {
        return $this->entity->getClientSecret();
    }

    /**
     * @return string[]
     */
    public function getRedirectUris()
    {
        return $this->entity->getRedirectUris();
    }

    /**
     * @return OidcGrantType
     */
    public function getGrantType()
    {
        return $this->entity->getGrantType();
    }

    /**
     * @return bool
     */
    public function isEnablePlayground()
    {
        return $this->entity->isEnablePlayground();
    }

    /**
     * @return string
     */
    public function getProtocol()
    {
        return $this->entity->getProtocol();
    }

    /**
     * @return string
     */
    public function getAcsBinding()
    {
        return $this->entity->getAcsBinding();
    }

    /**
     * @return string
     */
    public function getNameIdFormat()
    {
        return $this->entity->getNameIdFormat();
    }

    /**
     * @return string
     */
    public function getEntityId()
    {
        return $this->entity->getEntityId();
    }

    /**
     * @return string
     */
    public function getCertificate()
    {
        return $this->entity->getCertificate();
    }

    /**
     * @return string
     */
    public function getLogoUrl()
    {
        return $this->entity->getLogoUrl();
    }

    /**
     * @return string
     */
    public function getNameNl()
    {
        return $this->entity->getNameNl();
    }

    /**
     * @return string
     */
    public function getNameEn()
    {
        return $this->entity->getNameEn();
    }

    /**
     * @return string
     */
    public function getDescriptionNl()
    {
        return $this->entity->getDescriptionNl();
    }

    /**
     * @return string
     */
    public function getDescriptionEn()
    {
        return $this->entity->getDescriptionEn();
    }

    /**
     * @return string
     */
    public function getApplicationUrl()
    {
        return $this->entity->getApplicationUrl();
    }

    /**
     * @return string
     */
    public function getEulaUrl()
    {
        return $this->entity->getEulaUrl();
    }

    /**
     * @return ContactPerson|null
     */
    public function getAdministrativeContact()
    {
        $administrativeContact = $this->entity->getAdministrativeContact();
        if (!is_null($administrativeContact) && !$administrativeContact->isContactSet()) {
            return null;
        }
        return $administrativeContact;
    }

    /**
     * @return ContactPerson|null
     */
    public function getTechnicalContact()
    {
        $technicalContact = $this->entity->getTechnicalContact();
        if (!is_null($technicalContact) && !$technicalContact->isContactSet()) {
            return null;
        }
        return $technicalContact;
    }

    /**
     * @return ContactPerson|null
     */
    public function getSupportContact()
    {
        $supportContact = $this->entity->getSupportContact();
        if (!is_null($supportContact) && !$supportContact->isContactSet()) {
            return null;
        }
        return $supportContact;
    }

    /**
     * @return Attribute
     */
    public function getGivenNameAttribute()
    {
        return $this->entity->getGivenNameAttribute();
    }

    /**
     * @return Attribute
     */
    public function getSurNameAttribute()
    {
        return $this->entity->getSurNameAttribute();
    }

    /**
     * @return Attribute
     */
    public function getCommonNameAttribute()
    {
        return $this->entity->getCommonNameAttribute();
    }

    /**
     * @return Attribute
     */
    public function getDisplayNameAttribute()
    {
        return $this->entity->getDisplayNameAttribute();
    }

    /**
     * @return Attribute
     */
    public function getEmailAddressAttribute()
    {
        return $this->entity->getEmailAddressAttribute();
    }

    /**
     * @return Attribute
     */
    public function getOrganizationAttribute()
    {
        return $this->entity->getOrganizationAttribute();
    }

    /**
     * @return Attribute
     */
    public function getOrganizationTypeAttribute()
    {
        return $this->entity->getOrganizationTypeAttribute();
    }

    /**
     * @return Attribute
     */
    public function getAffiliationAttribute()
    {
        return $this->entity->getAffiliationAttribute();
    }

    /**
     * @return Attribute
     */
    public function getEntitlementAttribute()
    {
        return $this->entity->getEntitlementAttribute();
    }

    /**
     * @return Attribute
     */
    public function getPrincipleNameAttribute()
    {
        return $this->entity->getPrincipleNameAttribute();
    }

    /**
     * @return Attribute
     */
    public function getUidAttribute()
    {
        return $this->entity->getUidAttribute();
    }

    /**
     * @return Attribute
     */
    public function getPreferredLanguageAttribute()
    {
        return $this->entity->getPreferredLanguageAttribute();
    }

    /**
     * @return Attribute
     */
    public function getPersonalCodeAttribute()
    {
        return $this->entity->getPersonalCodeAttribute();
    }

    /**
     * @return Attribute
     */
    public function getScopedAffiliationAttribute()
    {
        return $this->entity->getScopedAffiliationAttribute();
    }

    /**
     * @return Attribute
     */
    public function getEduPersonTargetedIDAttribute()
    {
        return $this->entity->getEduPersonTargetedIDAttribute();
    }

    /**
     * @return string
     */
    public function getComments()
    {
        return $this->entity->getComments();
    }

    /**
     * @return bool
     */
    public function hasComments()
    {
        return !(empty($this->getComments()));
    }

    /**
     * @return string
     */
    public function getOrganizationNameEn()
    {
        return $this->entity->getOrganizationNameEn();
    }

    /**
     * @return string
     */
    public function getOrganizationDisplayNameEn()
    {
        return $this->entity->getOrganizationDisplayNameEn();
    }

    /**
     * @return string
     */
    public function getOrganizationUrlEn()
    {
        return $this->entity->getOrganizationUrlEn();
    }

    /**
     * @return string
     */
    public function getOrganizationNameNl()
    {
        return $this->entity->getOrganizationNameNl();
    }

    /**
     * @return string
     */
    public function getOrganizationDisplayNameNl()
    {
        return $this->entity->getOrganizationDisplayNameNl();
    }

    /**
     * @return string
     */
    public function getOrganizationUrlNl()
    {
        return $this->entity->getOrganizationUrlNl();
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->entity->getStatus();
    }

    public function isDraft()
    {
        return $this->entity->isDraft();
    }

    public function isPublished()
    {
        return $this->entity->isPublished();
    }

    public function isProduction()
    {
        return $this->entity->isProduction();
    }

    /**
     * @param IdentityProvider $provider
     * @return bool
     */
    public function isWhitelisted(IdentityProvider $provider)
    {
        return in_array($provider->getEntityId(), $this->getIdpWhitelist());
    }

    /**
     * @return string[]
     */
    public function getIdpWhitelist()
    {
        return $this->entity->getIdpWhitelist();
    }

    /**
     * @return bool
     */
    public function isIdpAllowAll()
    {
        return $this->entity->isIdpAllowAll();
    }

    /**
     * @return int
     */
    public function getAccessTokenValidity()
    {
        return $this->entity->getAccessTokenValidity();
    }

    /**
     * @return bool
     */
    public function isPublicClient()
    {
        return $this->entity->isPublicClient();
    }

    /**
     * @return ResourceServerCollection
     */
    public function getOidcngResourceServers()
    {
        return $this->entity->getOidcngResourceServers();
    }

    public function getArpAttributes()
    {
        return $this->manageEntity->getAttributes();
    }

    public function isManageEntity()
    {
        return !is_null($this->manageEntity);
    }

    public function isExcludedFromPush()
    {
        return $this->manageEntity->isExcludedFromPush();
    }

    public function isExcludedFromPushSet()
    {
        return $this->manageEntity->isExcludedFromPushSet();
    }

    /**
     * @return array
     */
    public function getScopes()
    {
        return $this->manageEntity->getOidcClient()->getScope();
    }
}
