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

namespace Surfnet\ServiceProviderDashboard\Application\Command\Entity;

use Surfnet\ServiceProviderDashboard\Application\Command\Command;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Contact;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Validator\Constraints as SpDashboardAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class SaveEntityCommand implements Command
{
    /**
     * @var string
     * @Assert\Uuid
     */
    private $id;

    /**
     * @var Service
     */
    private $service;

    /**
     * @var string
     */
    private $ticketNumber;

    /**
     * @var bool
     */
    private $archived = false;

    /**
     * @var string
     *
     * @Assert\NotBlank(groups={"creation"})
     * @Assert\Choice(choices = {"production", "test"}, groups={"creation"})
     */
    private $environment = Entity::ENVIRONMENT_TEST;

    /**
     * Metadata URL that import last happened from.
     *
     * @var string

     */
    private $importUrl;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Url(
     *      protocols={"https"},
     *      message = "url.notSecure",
     *      groups={"finished"}
     * )
     */
    private $metadataUrl;

    /**
     * @var string
     */
    private $pastedMetadata;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Url(protocols={"https","http"})
     * @Assert\Url(
     *      protocols={"https"},
     *      message = "url.notSecure",
     *      groups={"finished"}
     * )
     */
    private $acsLocation;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Url()
     * @SpDashboardAssert\ValidEntityId()
     */
    private $entityId;

    /**
     * @var string
     *
     * @SpDashboardAssert\ValidSSLCertificate()
     */
    private $certificate;

    /**
     * @var string
     *
     * @Assert\Url()
     * @SpDashboardAssert\ValidLogo()
     * @Assert\NotBlank()
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
     *
     * @Assert\Length(max = 300)
     */
    private $descriptionNl;

    /**
     * @var string
     *
     * @Assert\Length(max = 300)
     */
    private $descriptionEn;

    /**
     * @var string
     *
     * @Assert\Url()
     */
    private $applicationUrl;

    /**
     * @var string
     *
     * @Assert\Url()
     */
    private $eulaUrl;

    /**
     * @var Contact
     *
     * @Assert\Type(type="Surfnet\ServiceProviderDashboard\Domain\ValueObject\Contact")
     * @Assert\NotBlank()
     * @Assert\Valid()
     */
    private $administrativeContact;

    /**
     * @var Contact
     *
     * @Assert\Type(type="Surfnet\ServiceProviderDashboard\Domain\ValueObject\Contact")
     * @Assert\NotBlank()
     * @Assert\Valid()
     */
    private $technicalContact;

    /**
     * @var Contact
     *
     * @Assert\Type(type="Surfnet\ServiceProviderDashboard\Domain\ValueObject\Contact")
     * @Assert\NotBlank()
     * @Assert\Valid()
     */
    private $supportContact;

    /**
     * @var Attribute
     *
     * @Assert\Type(type="Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute")
     * @Assert\Valid()
     */
    private $givenNameAttribute;

    /**
     * @var Attribute
     *
     * @Assert\Type(type="Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute")
     * @Assert\Valid()
     */
    private $surNameAttribute;

    /**
     * @var Attribute
     *
     * @Assert\Type(type="Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute")
     * @Assert\Valid()
     */
    private $commonNameAttribute;

    /**
     * @var Attribute
     *
     * @Assert\Type(type="Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute")
     * @Assert\Valid()
     */
    private $displayNameAttribute;

    /**
     * @var Attribute
     *
     * @Assert\Type(type="Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute")
     * @Assert\Valid()
     */
    private $emailAddressAttribute;

    /**
     * @var Attribute
     *
     * @Assert\Type(type="Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute")
     * @Assert\Valid()
     */
    private $organizationAttribute;

    /**
     * @var Attribute
     *
     * @Assert\Type(type="Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute")
     * @Assert\Valid()
     */
    private $organizationTypeAttribute;

    /**
     * @var Attribute
     *
     * @Assert\Type(type="Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute")
     * @Assert\Valid()
     */
    private $affiliationAttribute;

    /**
     * @var Attribute
     *
     * @Assert\Type(type="Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute")
     * @Assert\Valid()
     */
    private $entitlementAttribute;

    /**
     * @var Attribute
     *
     * @Assert\Type(type="Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute")
     * @Assert\Valid()
     */
    private $principleNameAttribute;

    /**
     * @var Attribute
     *
     * @Assert\Type(type="Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute")
     * @Assert\Valid()
     */
    private $uidAttribute;

    /**
     * @var Attribute
     *
     * @Assert\Type(type="Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute")
     * @Assert\Valid()
     */
    private $preferredLanguageAttribute;

    /**
     * @var Attribute
     *
     * @Assert\Type(type="Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute")
     * @Assert\Valid()
     */
    private $personalCodeAttribute;

    /**
     * @var Attribute
     *
     * @Assert\Type(type="Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute")
     * @Assert\Valid()
     */
    private $scopedAffiliationAttribute;

    /**
     * @var Attribute
     *
     * @Assert\Type(type="Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute")
     * @Assert\Valid()
     */
    private $eduPersonTargetedIDAttribute;

    /**
     * @var string
     */
    private $comments;

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

    private function __construct()
    {
    }

    /**
     * @param Service $service
     * @return SaveEntityCommand
     */
    public static function forCreateAction(Service $service)
    {
        $command = new self();
        $command->service = $service;
        return $command;
    }

    /**
     * @param Entity $entity
     *
     * @return SaveEntityCommand
     */
    public static function fromEntity(Entity $entity)
    {
        $command = new self();
        $command->id = $entity->getId();
        $command->service = $entity->getService();
        $command->ticketNumber = $entity->getTicketNumber();
        $command->archived = $entity->isArchived();
        $command->environment = $entity->getEnvironment();
        $command->importUrl = $entity->getImportUrl();
        $command->pastedMetadata = $entity->getPastedMetadata();
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
        $command->comments = $entity->getComments();
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
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Service
     */
    public function getService()
    {
        return $this->service;
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
     * @param string $ticketNo
     */
    public function setTicketNumber($ticketNo)
    {
        $this->ticketNumber = $ticketNo;
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
    public function getPastedMetadata()
    {
        return $this->pastedMetadata;
    }

    /**
     * @param string $pastedMetadata
     */
    public function setPastedMetadata($pastedMetadata)
    {
        $this->pastedMetadata = $pastedMetadata;
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
     * @return Contact
     */
    public function getAdministrativeContact()
    {
        return $this->administrativeContact;
    }

    /**
     * @param Contact $administrativeContact
     */
    public function setAdministrativeContact($administrativeContact)
    {
        $this->administrativeContact = $administrativeContact;
    }

    /**
     * @return Contact
     */
    public function getTechnicalContact()
    {
        return $this->technicalContact;
    }

    /**
     * @param Contact $technicalContact
     */
    public function setTechnicalContact($technicalContact)
    {
        $this->technicalContact = $technicalContact;
    }

    /**
     * @return Contact
     */
    public function getSupportContact()
    {
        return $this->supportContact;
    }

    /**
     * @param Contact $supportContact
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

    /**
     * @return string
     */
    public function getNameIdFormat()
    {
        return $this->nameIdFormat;
    }

    /**
     * @param string $nameIdFormat
     */
    public function setNameIdFormat($nameIdFormat)
    {
        $this->nameIdFormat = $nameIdFormat;
    }

    /**
     * @return string
     */
    public function getOrganizationNameNl()
    {
        return $this->organizationNameNl;
    }

    /**
     * @param string $organizationNameNl
     */
    public function setOrganizationNameNl($organizationNameNl)
    {
        $this->organizationNameNl = $organizationNameNl;
    }

    /**
     * @return string
     */
    public function getOrganizationNameEn()
    {
        return $this->organizationNameEn;
    }

    /**
     * @param string $organizationNameEn
     */
    public function setOrganizationNameEn($organizationNameEn)
    {
        $this->organizationNameEn = $organizationNameEn;
    }

    /**
     * @return string
     */
    public function getOrganizationDisplayNameNl()
    {
        return $this->organizationDisplayNameNl;
    }

    /**
     * @param string $organizationDisplayNameNl
     */
    public function setOrganizationDisplayNameNl($organizationDisplayNameNl)
    {
        $this->organizationDisplayNameNl = $organizationDisplayNameNl;
    }

    /**
     * @return string
     */
    public function getOrganizationDisplayNameEn()
    {
        return $this->organizationDisplayNameEn;
    }

    /**
     * @param string $organizationDisplayNameEn
     */
    public function setOrganizationDisplayNameEn($organizationDisplayNameEn)
    {
        $this->organizationDisplayNameEn = $organizationDisplayNameEn;
    }

    /**
     * @return string
     */
    public function getOrganizationUrlNl()
    {
        return $this->organizationUrlNl;
    }

    /**
     * @param string $organizationUrlNl
     */
    public function setOrganizationUrlNl($organizationUrlNl)
    {
        $this->organizationUrlNl = $organizationUrlNl;
    }

    /**
     * @return string
     */
    public function getOrganizationUrlEn()
    {
        return $this->organizationUrlEn;
    }

    /**
     * @param string $organizationUrlEn
     */
    public function setOrganizationUrlEn($organizationUrlEn)
    {
        $this->organizationUrlEn = $organizationUrlEn;
    }

    public function isForProduction()
    {
        return $this->environment === Entity::ENVIRONMENT_PRODUCTION;
    }

    public function setId($id)
    {
        $this->id = $id;
    }
}
