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

use Surfnet\ServiceProviderDashboard\Application\Parser\OidcClientIdParserTest;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity as DomainEntity;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Contact;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
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
     * @var string
     */
    private $acsLocation;

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
     * @var Attribute
     */
    private $givenNameAttribute;

    /**
     * @var Attribute
     */
    private $surNameAttribute;

    /**
     * @var Attribute
     */
    private $commonNameAttribute;

    /**
     * @var Attribute
     */
    private $displayNameAttribute;

    /**
     * @var Attribute
     */
    private $emailAddressAttribute;

    /**
     * @var Attribute
     */
    private $organizationAttribute;

    /**
     * @var Attribute
     */
    private $organizationTypeAttribute;

    /**
     * @var Attribute
     */
    private $affiliationAttribute;

    /**
     * @var Attribute
     */
    private $entitlementAttribute;

    /**
     * @var Attribute
     */
    private $principleNameAttribute;

    /**
     * @var Attribute
     */
    private $uidAttribute;

    /**
     * @var Attribute
     */
    private $preferredLanguageAttribute;

    /**
     * @var Attribute
     */
    private $personalCodeAttribute;

    /**
     * @var Attribute
     */
    private $scopedAffiliationAttribute;

    /**
     * @var Attribute
     */
    private $eduPersonTargetedIDAttribute;

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
     * @var string
     */
    private $grantType;

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

    private function __construct()
    {
    }

    /**
     * @param DomainEntity $entity
     *
     * @return EntityDetail
     */
    public static function fromEntity(DomainEntity $entity)
    {
        $entityDetail = new self();
        $entityDetail->id = $entity->getId();
        $entityDetail->manageId = $entity->getManageId();
        $entityDetail->protocol = $entity->getProtocol();
        if ($entity->getProtocol() == DomainEntity::TYPE_OPENID_CONNECT) {
            $entityDetail->grantType = $entity->getGrantType()->getGrantType();
            $entityDetail->redirectUris = $entity->getRedirectUris();
            $entityDetail->playgroundEnabled = $entity->isEnablePlayground();
        }

        if ($entity->getProtocol() == DomainEntity::TYPE_OPENID_CONNECT_TNG) {
            $entityDetail->grantType = $entity->getGrantType()->getGrantType();
            $entityDetail->isPublicClient = $entity->isPublicClient();
            $entityDetail->accessTokenValidity = $entity->getAccessTokenValidity();
            $entityDetail->redirectUris = $entity->getRedirectUris();
            $entityDetail->playgroundEnabled = $entity->isEnablePlayground();
        }

        $entityDetail->metadataUrl = $entity->getMetadataUrl();
        $entityDetail->acsLocation = $entity->getAcsLocation();
        $entityDetail->entityId = $entity->getEntityId();
        $entityDetail->certificate = $entity->getCertificate();
        $entityDetail->logoUrl = $entity->getLogoUrl();
        $entityDetail->nameNl = $entity->getNameNl();
        $entityDetail->nameEn = $entity->getNameEn();
        $entityDetail->descriptionNl = $entity->getDescriptionNl();
        $entityDetail->descriptionEn = $entity->getDescriptionEn();
        $entityDetail->applicationUrl = $entity->getApplicationUrl();
        $entityDetail->eulaUrl = $entity->getEulaUrl();
        $entityDetail->administrativeContact = $entity->getAdministrativeContact();
        $entityDetail->technicalContact = $entity->getTechnicalContact();
        $entityDetail->supportContact = $entity->getSupportContact();
        $entityDetail->givenNameAttribute = $entity->getGivenNameAttribute();
        $entityDetail->surNameAttribute = $entity->getSurNameAttribute();
        $entityDetail->commonNameAttribute = $entity->getCommonNameAttribute();
        $entityDetail->displayNameAttribute = $entity->getDisplayNameAttribute();
        $entityDetail->emailAddressAttribute = $entity->getEmailAddressAttribute();
        $entityDetail->organizationAttribute = $entity->getOrganizationAttribute();
        $entityDetail->organizationTypeAttribute = $entity->getOrganizationTypeAttribute();
        $entityDetail->affiliationAttribute = $entity->getAffiliationAttribute();
        $entityDetail->entitlementAttribute = $entity->getEntitlementAttribute();
        $entityDetail->principleNameAttribute = $entity->getPrincipleNameAttribute();
        $entityDetail->uidAttribute = $entity->getUidAttribute();
        $entityDetail->preferredLanguageAttribute = $entity->getPreferredLanguageAttribute();
        $entityDetail->personalCodeAttribute = $entity->getPersonalCodeAttribute();
        $entityDetail->scopedAffiliationAttribute = $entity->getScopedAffiliationAttribute();
        $entityDetail->eduPersonTargetedIDAttribute = $entity->getEduPersonTargetedIDAttribute();
        $entityDetail->nameIdFormat = $entity->getNameIdFormat();
        $entityDetail->organizationNameNl = $entity->getOrganizationNameNl();
        $entityDetail->organizationNameEn = $entity->getOrganizationNameEn();
        $entityDetail->organizationDisplayNameNl = $entity->getOrganizationDisplayNameNl();
        $entityDetail->organizationDisplayNameEn = $entity->getOrganizationDisplayNameEn();
        $entityDetail->organizationUrlNl = $entity->getOrganizationUrlNl();
        $entityDetail->organizationUrlEn = $entity->getOrganizationUrlEn();

        $actionId = $entityDetail->manageId;
        if ($entityDetail->isLocalEntity()) {
            $actionId = $entityDetail->id;
        }
        $entityDetail->actions = new EntityActions(
            $actionId,
            $entity->getService()->getId(),
            $entity->getStatus(),
            $entity->getEnvironment(),
            $entity->getProtocol()
        );
        return $entityDetail;
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
     * @return string
     */
    public function getAcsLocation()
    {
        return $this->acsLocation;
    }

    /**
     * @return string
     */
    public function getEntityId()
    {
        if ($this->getProtocol() !== DomainEntity::TYPE_OPENID_CONNECT) {
            return $this->entityId;
        }
        return OidcClientIdParserTest::parse($this->entityId);
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

    /**
     * @return Attribute
     */
    public function getGivenNameAttribute()
    {
        return $this->givenNameAttribute;
    }

    /**
     * @return Attribute
     */
    public function getSurNameAttribute()
    {
        return $this->surNameAttribute;
    }

    /**
     * @return Attribute
     */
    public function getCommonNameAttribute()
    {
        return $this->commonNameAttribute;
    }

    /**
     * @return Attribute
     */
    public function getDisplayNameAttribute()
    {
        return $this->displayNameAttribute;
    }

    /**
     * @return Attribute
     */
    public function getEmailAddressAttribute()
    {
        return $this->emailAddressAttribute;
    }

    /**
     * @return Attribute
     */
    public function getOrganizationAttribute()
    {
        return $this->organizationAttribute;
    }

    /**
     * @return Attribute
     */
    public function getOrganizationTypeAttribute()
    {
        return $this->organizationTypeAttribute;
    }

    /**
     * @return Attribute
     */
    public function getAffiliationAttribute()
    {
        return $this->affiliationAttribute;
    }

    /**
     * @return Attribute
     */
    public function getEntitlementAttribute()
    {
        return $this->entitlementAttribute;
    }

    /**
     * @return Attribute
     */
    public function getPrincipleNameAttribute()
    {
        return $this->principleNameAttribute;
    }

    /**
     * @return Attribute
     */
    public function getUidAttribute()
    {
        return $this->uidAttribute;
    }

    /**
     * @return Attribute
     */
    public function getPreferredLanguageAttribute()
    {
        return $this->preferredLanguageAttribute;
    }

    /**
     * @return Attribute
     */
    public function getPersonalCodeAttribute()
    {
        return $this->personalCodeAttribute;
    }

    /**
     * @return Attribute
     */
    public function getScopedAffiliationAttribute()
    {
        return $this->scopedAffiliationAttribute;
    }

    /**
     * @return Attribute
     */
    public function getEduPersonTargetedIDAttribute()
    {
        return $this->eduPersonTargetedIDAttribute;
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

    /**
     * @return string
     */
    public function getGrantType()
    {
        return $this->grantType;
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
}
