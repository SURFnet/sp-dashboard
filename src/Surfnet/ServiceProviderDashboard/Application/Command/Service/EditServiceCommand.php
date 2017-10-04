<?php

/**
 * Copyright 2017 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Application\Command\Service;

use Surfnet\ServiceProviderDashboard\Application\Command\Command;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Supplier;
use Surfnet\ServiceProviderDashboard\Domain\Model\Attribute;
use Surfnet\ServiceProviderDashboard\Domain\Model\Contact;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class EditServiceCommand implements Command
{
    /**
     * @var string
     * @Assert\NotBlank
     * @Assert\Uuid
     */
    private $id;

    /**
     * @var Supplier
     * @Assert\NotNull
     */
    private $supplier;

    /**
     * @var string
     * @Assert\NotBlank
     */
    private $ticketNumber;

    /**
     * @var bool
     */
    private $archived = false;

    /**
     * @var string
     */
    private $environment = Service::ENVIRONMENT_CONNECT;

    /**
     * @var int
     */
    private $status = Service::STATE_DRAFT;

    /**
     * @var string
     */
    private $janusId;

    /**
     * Metadata URL that import last happened from.
     *
     * @var string
     */
    private $importUrl;

    /**
     * @var string
     */
    private $metadataUrl;

    /**
     * SAML XML Metadata for entity.
     *
     * Imported from metadataurl.
     *
     * @var string
     */
    private $metadataXml;

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
     * @var ContactPerson
     */
    private $administrativeContact;

    /**
     * @var ContactPerson
     */
    private $technicalContact;

    /**
     * @var ContactPerson
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
    private $comments;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     *
     * @param string $id
     * @param Supplier $supplier
     * @param string $ticketNumber
     * @param bool $archived
     * @param string $environment
     * @param int $status
     * @param string $janusId
     * @param string $importUrl
     * @param string $metadataUrl
     * @param string $metadataXml
     * @param string $acsLocation
     * @param string $entityId
     * @param string $certificate
     * @param string $logoUrl
     * @param string $nameNl
     * @param string $nameEn
     * @param string $descriptionNl
     * @param string $descriptionEn
     * @param string $applicationUrl
     * @param string $eulaUrl
     * @param Contact $administrativeContact
     * @param Contact $technicalContact
     * @param Contact $supportContact
     * @param Attribute $givenNameAttribute
     * @param Attribute $surNameAttribute
     * @param Attribute $commonNameAttribute
     * @param Attribute $displayNameAttribute
     * @param Attribute $emailAddressAttribute
     * @param Attribute $organizationAttribute
     * @param Attribute $organizationTypeAttribute
     * @param Attribute $affiliationAttribute
     * @param Attribute $entitlementAttribute
     * @param Attribute $principleNameAttribute
     * @param Attribute $uidAttribute
     * @param Attribute $preferredLanguageAttribute
     * @param Attribute $personalCodeAttribute
     * @param Attribute $scopedAffiliationAttribute
     * @param Attribute $eduPersonTargetedIDAttribute
     * @param string $comments
     */
    public function __construct(
        $id,
        Supplier $supplier,
        $ticketNumber,
        $archived,
        $environment,
        $status,
        $janusId,
        $importUrl,
        $metadataUrl,
        $metadataXml,
        $acsLocation,
        $entityId,
        $certificate,
        $logoUrl,
        $nameNl,
        $nameEn,
        $descriptionNl,
        $descriptionEn,
        $applicationUrl,
        $eulaUrl,
        $administrativeContact,
        $technicalContact,
        $supportContact,
        $givenNameAttribute,
        $surNameAttribute,
        $commonNameAttribute,
        $displayNameAttribute,
        $emailAddressAttribute,
        $organizationAttribute,
        $organizationTypeAttribute,
        $affiliationAttribute,
        $entitlementAttribute,
        $principleNameAttribute,
        $uidAttribute,
        $preferredLanguageAttribute,
        $personalCodeAttribute,
        $scopedAffiliationAttribute,
        $eduPersonTargetedIDAttribute,
        $comments
    ) {
        $this->id = $id;
        $this->supplier = $supplier;
        $this->ticketNumber = $ticketNumber;
        $this->archived = $archived;
        $this->environment = $environment;
        $this->status = $status;
        $this->janusId = $janusId;
        $this->importUrl = $importUrl;
        $this->metadataUrl = $metadataUrl;
        $this->metadataXml = $metadataXml;
        $this->acsLocation = $acsLocation;
        $this->entityId = $entityId;
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
        $this->givenNameAttribute = $givenNameAttribute;
        $this->surNameAttribute = $surNameAttribute;
        $this->commonNameAttribute = $commonNameAttribute;
        $this->displayNameAttribute = $displayNameAttribute;
        $this->emailAddressAttribute = $emailAddressAttribute;
        $this->organizationAttribute = $organizationAttribute;
        $this->organizationTypeAttribute = $organizationTypeAttribute;
        $this->affiliationAttribute = $affiliationAttribute;
        $this->entitlementAttribute = $entitlementAttribute;
        $this->principleNameAttribute = $principleNameAttribute;
        $this->uidAttribute = $uidAttribute;
        $this->preferredLanguageAttribute = $preferredLanguageAttribute;
        $this->personalCodeAttribute = $personalCodeAttribute;
        $this->scopedAffiliationAttribute = $scopedAffiliationAttribute;
        $this->eduPersonTargetedIDAttribute = $eduPersonTargetedIDAttribute;
        $this->comments = $comments;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Supplier
     */
    public function getSupplier()
    {
        return $this->supplier;
    }

    /**
     * @return string
     */
    public function getTicketNumber()
    {
        return $this->ticketNumber;
    }

    /**
     * @return bool
     */
    public function isArchived()
    {
        return $this->archived;
    }

    /**
     * @param bool $archived
     */
    public function setArchived($archived)
    {
        $this->archived = $archived;
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @param string $environment
     */
    public function setEnvironment($environment)
    {
        $this->environment = $environment;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @param string $ticketNo
     */
    public function setTicketNo($ticketNo)
    {
        $this->ticketNo = $ticketNo;
    }

    /**
     * @return string
     */
    public function getJanusId()
    {
        return $this->janusId;
    }

    /**
     * @param string $janusId
     */
    public function setJanusId($janusId)
    {
        $this->janusId = $janusId;
    }

    /**
     * @return string
     */
    public function getImportUrl()
    {
        return $this->importUrl;
    }

    /**
     * @param string $importUrl
     */
    public function setImportUrl($importUrl)
    {
        $this->importUrl = $importUrl;
    }

    /**
     * @return string
     */
    public function getMetadataUrl()
    {
        return $this->metadataUrl;
    }

    /**
     * @param string $metadataUrl
     */
    public function setMetadataUrl($metadataUrl)
    {
        $this->metadataUrl = $metadataUrl;
    }

    /**
     * @return string
     */
    public function getMetadataXml()
    {
        return $this->metadataXml;
    }

    /**
     * @param string $metadataXml
     */
    public function setMetadataXml($metadataXml)
    {
        $this->metadataXml = $metadataXml;
    }

    /**
     * @return string
     */
    public function getAcsLocation()
    {
        return $this->acsLocation;
    }

    /**
     * @param string $acsLocation
     */
    public function setAcsLocation($acsLocation)
    {
        $this->acsLocation = $acsLocation;
    }

    /**
     * @return string
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @param string $entityId
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;
    }

    /**
     * @return string
     */
    public function getCertificate()
    {
        return $this->certificate;
    }

    /**
     * @param string $certificate
     */
    public function setCertificate($certificate)
    {
        $this->certificate = $certificate;
    }

    /**
     * @return string
     */
    public function getLogoUrl()
    {
        return $this->logoUrl;
    }

    /**
     * @param string $logoUrl
     */
    public function setLogoUrl($logoUrl)
    {
        $this->logoUrl = $logoUrl;
    }

    /**
     * @return string
     */
    public function getNameNl()
    {
        return $this->nameNl;
    }

    /**
     * @param string $nameNl
     */
    public function setNameNl($nameNl)
    {
        $this->nameNl = $nameNl;
    }

    /**
     * @return string
     */
    public function getNameEn()
    {
        return $this->nameEn;
    }

    /**
     * @param string $nameEn
     */
    public function setNameEn($nameEn)
    {
        $this->nameEn = $nameEn;
    }

    /**
     * @return string
     */
    public function getDescriptionNl()
    {
        return $this->descriptionNl;
    }

    /**
     * @param string $descriptionNl
     */
    public function setDescriptionNl($descriptionNl)
    {
        $this->descriptionNl = $descriptionNl;
    }

    /**
     * @return string
     */
    public function getDescriptionEn()
    {
        return $this->descriptionEn;
    }

    /**
     * @param string $descriptionEn
     */
    public function setDescriptionEn($descriptionEn)
    {
        $this->descriptionEn = $descriptionEn;
    }

    /**
     * @return string
     */
    public function getApplicationUrl()
    {
        return $this->applicationUrl;
    }

    /**
     * @param string $applicationUrl
     */
    public function setApplicationUrl($applicationUrl)
    {
        $this->applicationUrl = $applicationUrl;
    }

    /**
     * @return string
     */
    public function getEulaUrl()
    {
        return $this->eulaUrl;
    }

    /**
     * @param string $eulaUrl
     */
    public function setEulaUrl($eulaUrl)
    {
        $this->eulaUrl = $eulaUrl;
    }

    /**
     * @return ContactPerson
     */
    public function getAdministrativeContact()
    {
        return $this->administrativeContact;
    }

    /**
     * @param ContactPerson $administrativeContact
     */
    public function setAdministrativeContact($administrativeContact)
    {
        $this->administrativeContact = $administrativeContact;
    }

    /**
     * @return ContactPerson
     */
    public function getTechnicalContact()
    {
        return $this->technicalContact;
    }

    /**
     * @param ContactPerson $technicalContact
     */
    public function setTechnicalContact($technicalContact)
    {
        $this->technicalContact = $technicalContact;
    }

    /**
     * @return ContactPerson
     */
    public function getSupportContact()
    {
        return $this->supportContact;
    }

    /**
     * @param ContactPerson $supportContact
     */
    public function setSupportContact($supportContact)
    {
        $this->supportContact = $supportContact;
    }

    /**
     * @return Attribute
     */
    public function getGivenNameAttribute()
    {
        return $this->givenNameAttribute;
    }

    /**
     * @param Attribute $givenNameAttribute
     */
    public function setGivenNameAttribute($givenNameAttribute)
    {
        $this->givenNameAttribute = $givenNameAttribute;
    }

    /**
     * @return Attribute
     */
    public function getSurNameAttribute()
    {
        return $this->surNameAttribute;
    }

    /**
     * @param Attribute $surNameAttribute
     */
    public function setSurNameAttribute($surNameAttribute)
    {
        $this->surNameAttribute = $surNameAttribute;
    }

    /**
     * @return Attribute
     */
    public function getCommonNameAttribute()
    {
        return $this->commonNameAttribute;
    }

    /**
     * @param Attribute $commonNameAttribute
     */
    public function setCommonNameAttribute($commonNameAttribute)
    {
        $this->commonNameAttribute = $commonNameAttribute;
    }

    /**
     * @return Attribute
     */
    public function getDisplayNameAttribute()
    {
        return $this->displayNameAttribute;
    }

    /**
     * @param Attribute $displayNameAttribute
     */
    public function setDisplayNameAttribute($displayNameAttribute)
    {
        $this->displayNameAttribute = $displayNameAttribute;
    }

    /**
     * @return Attribute
     */
    public function getEmailAddressAttribute()
    {
        return $this->emailAddressAttribute;
    }

    /**
     * @param Attribute $emailAddressAttribute
     */
    public function setEmailAddressAttribute($emailAddressAttribute)
    {
        $this->emailAddressAttribute = $emailAddressAttribute;
    }

    /**
     * @return Attribute
     */
    public function getOrganizationAttribute()
    {
        return $this->organizationAttribute;
    }

    /**
     * @param Attribute $organizationAttribute
     */
    public function setOrganizationAttribute($organizationAttribute)
    {
        $this->organizationAttribute = $organizationAttribute;
    }

    /**
     * @return Attribute
     */
    public function getOrganizationTypeAttribute()
    {
        return $this->organizationTypeAttribute;
    }

    /**
     * @param Attribute $organizationTypeAttribute
     */
    public function setOrganizationTypeAttribute($organizationTypeAttribute)
    {
        $this->organizationTypeAttribute = $organizationTypeAttribute;
    }

    /**
     * @return Attribute
     */
    public function getAffiliationAttribute()
    {
        return $this->affiliationAttribute;
    }

    /**
     * @param Attribute $affiliationAttribute
     */
    public function setAffiliationAttribute($affiliationAttribute)
    {
        $this->affiliationAttribute = $affiliationAttribute;
    }

    /**
     * @return Attribute
     */
    public function getEntitlementAttribute()
    {
        return $this->entitlementAttribute;
    }

    /**
     * @param Attribute $entitlementAttribute
     */
    public function setEntitlementAttribute($entitlementAttribute)
    {
        $this->entitlementAttribute = $entitlementAttribute;
    }

    /**
     * @return Attribute
     */
    public function getPrincipleNameAttribute()
    {
        return $this->principleNameAttribute;
    }

    /**
     * @param Attribute $principleNameAttribute
     */
    public function setPrincipleNameAttribute($principleNameAttribute)
    {
        $this->principleNameAttribute = $principleNameAttribute;
    }

    /**
     * @return Attribute
     */
    public function getUidAttribute()
    {
        return $this->uidAttribute;
    }

    /**
     * @param Attribute $uidAttribute
     */
    public function setUidAttribute($uidAttribute)
    {
        $this->uidAttribute = $uidAttribute;
    }

    /**
     * @return Attribute
     */
    public function getPreferredLanguageAttribute()
    {
        return $this->preferredLanguageAttribute;
    }

    /**
     * @param Attribute $preferredLanguageAttribute
     */
    public function setPreferredLanguageAttribute($preferredLanguageAttribute)
    {
        $this->preferredLanguageAttribute = $preferredLanguageAttribute;
    }

    /**
     * @return Attribute
     */
    public function getPersonalCodeAttribute()
    {
        return $this->personalCodeAttribute;
    }

    /**
     * @param Attribute $personalCodeAttribute
     */
    public function setPersonalCodeAttribute($personalCodeAttribute)
    {
        $this->personalCodeAttribute = $personalCodeAttribute;
    }

    /**
     * @return Attribute
     */
    public function getScopedAffiliationAttribute()
    {
        return $this->scopedAffiliationAttribute;
    }

    /**
     * @param Attribute $scopedAffiliationAttribute
     */
    public function setScopedAffiliationAttribute($scopedAffiliationAttribute)
    {
        $this->scopedAffiliationAttribute = $scopedAffiliationAttribute;
    }

    /**
     * @return Attribute
     */
    public function getEduPersonTargetedIDAttribute()
    {
        return $this->eduPersonTargetedIDAttribute;
    }

    /**
     * @param Attribute $eduPersonTargetedIDAttribute
     */
    public function setEduPersonTargetedIDAttribute($eduPersonTargetedIDAttribute)
    {
        $this->eduPersonTargetedIDAttribute = $eduPersonTargetedIDAttribute;
    }

    /**
     * @return string
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param string $comments
     */
    public function setComments($comments)
    {
        $this->comments = $comments;
    }
}
