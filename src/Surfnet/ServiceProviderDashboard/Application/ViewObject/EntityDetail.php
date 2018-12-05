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

use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity as DomainEntity;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Contact;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class EntityDetail
{
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
     * @var string
     */
    private $manageId;

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
        $command = new self();
        $command->manageId = $entity->getManageId();
        $command->environment = $entity->getEnvironment();
        $command->metadataUrl = $entity->getMetadataUrl();
        $command->acsLocation = $entity->getAcsLocation();
        $command->entityId = $entity->getEntityId();
        $command->certificate = $entity->getCertificate();
        $command->logoUrl = $entity->getLogoUrl();
        $command->nameNl = $entity->getNameNl();
        $command->nameEn = $entity->getNameEn();
        $command->descriptionNl = $entity->getDescriptionNl();
        $command->descriptionEn = $entity->getDescriptionEn();
        $command->applicationUrl = $entity->getApplicationUrl();
        $command->eulaUrl = $entity->getEulaUrl();
        $command->administrativeContact = $entity->getAdministrativeContact();
        $command->technicalContact = $entity->getTechnicalContact();
        $command->supportContact = $entity->getSupportContact();
        $command->givenNameAttribute = $entity->getGivenNameAttribute();
        $command->surNameAttribute = $entity->getSurNameAttribute();
        $command->commonNameAttribute = $entity->getCommonNameAttribute();
        $command->displayNameAttribute = $entity->getDisplayNameAttribute();
        $command->emailAddressAttribute = $entity->getEmailAddressAttribute();
        $command->organizationAttribute = $entity->getOrganizationAttribute();
        $command->organizationTypeAttribute = $entity->getOrganizationTypeAttribute();
        $command->affiliationAttribute = $entity->getAffiliationAttribute();
        $command->entitlementAttribute = $entity->getEntitlementAttribute();
        $command->principleNameAttribute = $entity->getPrincipleNameAttribute();
        $command->uidAttribute = $entity->getUidAttribute();
        $command->preferredLanguageAttribute = $entity->getPreferredLanguageAttribute();
        $command->personalCodeAttribute = $entity->getPersonalCodeAttribute();
        $command->scopedAffiliationAttribute = $entity->getScopedAffiliationAttribute();
        $command->eduPersonTargetedIDAttribute = $entity->getEduPersonTargetedIDAttribute();
        $command->nameIdFormat = $entity->getNameIdFormat();
        $command->organizationNameNl = $entity->getOrganizationNameNl();
        $command->organizationNameEn = $entity->getOrganizationNameEn();
        $command->organizationDisplayNameNl = $entity->getOrganizationDisplayNameNl();
        $command->organizationDisplayNameEn = $entity->getOrganizationDisplayNameEn();
        $command->organizationUrlNl = $entity->getOrganizationUrlNl();
        $command->organizationUrlEn = $entity->getOrganizationUrlEn();

        return $command;
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
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
}
