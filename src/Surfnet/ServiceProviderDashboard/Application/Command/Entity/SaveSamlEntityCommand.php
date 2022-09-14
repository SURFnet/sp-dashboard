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

use InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
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
 *
 */
class SaveSamlEntityCommand implements SaveEntityCommandInterface
{
    /**
     * @var string
     * @Assert\Uuid
     */
    private $id;

    /**
     * @var string
     */
    private $status;

    /**
     * @var Service
     */
    private $service;

    /**
     * @var bool
     * @deprecated
     */
    private $archived = false;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Choice(choices = {"production", "test"}, strict=true)
     */
    private $environment = Constants::ENVIRONMENT_TEST;

    /**
     * Metadata URL that import last happened from.
     *
     * @var string
     * @deprecated
     */
    private $importUrl;

    /**
     * @var string
     *
     * @SpDashboardAssert\ValidMetadataUrl()
     */
    private $metadataUrl;

    /**
     * @var string
     * @deprecated
     */
    private $pastedMetadata;

    /**
     * @var array
     *
     * @Assert\All({
     *      @Assert\NotBlank(),
     *      @Assert\Url(
     *          protocols={"https"},
     *          message = "url.notSecure"
     *      )
     * })
     * @Assert\Count(
     *     max = 10,
     *     maxMessage = "{{ limit }} ASC locations or less are allowed"
     * )
     */
    private $acsLocations;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @SpDashboardAssert\ValidEntityId()
     * @SpDashboardAssert\UniqueEntityId()
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
     * @Assert\NotBlank()
     */
    private $nameNl;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    private $nameEn;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Length(max = 300)
     */
    private $descriptionNl;

    /**
     * @var string
     *
     * @Assert\NotBlank()
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
     * @Assert\Valid(groups={"production"})
     */
    private $administrativeContact;

    /**
     * @var Contact
     *
     * @Assert\Type(type="Surfnet\ServiceProviderDashboard\Domain\ValueObject\Contact")
     * @Assert\Valid()
     */
    private $technicalContact;

    /**
     * @var Contact
     *
     * @Assert\Type(type="Surfnet\ServiceProviderDashboard\Domain\ValueObject\Contact")
     * @Assert\Valid(groups={"production"})
     */
    private $supportContact;

    /**
     * @var Attribute
     *
     * @Assert\Type(type="Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute")
     * @SpDashboardAssert\ValidAttribute
     */
    private $givenNameAttribute;

    /**
     * @var Attribute
     *
     * @Assert\Type(type="Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute")
     * @SpDashboardAssert\ValidAttribute
     */
    private $surNameAttribute;

    /**
     * @var Attribute
     *
     * @Assert\Type(type="Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute")
     * @SpDashboardAssert\ValidAttribute
     */
    private $commonNameAttribute;

    /**
     * @var Attribute
     *
     * @Assert\Type(type="Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute")
     * @SpDashboardAssert\ValidAttribute
     */
    private $displayNameAttribute;

    /**
     * @var Attribute
     *
     * @Assert\Type(type="Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute")
     * @SpDashboardAssert\ValidAttribute
     */
    private $emailAddressAttribute;

    /**
     * @var Attribute
     *
     * @Assert\Type(type="Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute")
     * @SpDashboardAssert\ValidAttribute
     */
    private $organizationAttribute;

    /**
     * @var Attribute
     *
     * @Assert\Type(type="Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute")
     * @SpDashboardAssert\ValidAttribute
     */
    private $organizationTypeAttribute;

    /**
     * @var Attribute
     *
     * @Assert\Type(type="Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute")
     * @SpDashboardAssert\ValidAttribute
     */
    private $organizationUnitAttribute;

    /**
     * @var Attribute
     *
     * @Assert\Type(type="Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute")
     * @SpDashboardAssert\ValidAttribute
     */
    private $affiliationAttribute;

    /**
     * @var Attribute
     *
     * @Assert\Type(type="Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute")
     * @SpDashboardAssert\ValidAttribute
     */
    private $entitlementAttribute;

    /**
     * @var Attribute
     *
     * @Assert\Type(type="Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute")
     * @SpDashboardAssert\ValidAttribute
     */
    private $principleNameAttribute;

    /**
     * @var Attribute
     *
     * @Assert\Type(type="Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute")
     * @SpDashboardAssert\ValidAttribute
     */
    private $uidAttribute;

    /**
     * @var Attribute
     *
     * @Assert\Type(type="Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute")
     * @SpDashboardAssert\ValidAttribute
     */
    private $preferredLanguageAttribute;

    /**
     * @var Attribute
     *
     * @Assert\Type(type="Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute")
     * @SpDashboardAssert\ValidAttribute
     */
    private $personalCodeAttribute;

    /**
     * @var Attribute
     *
     * @Assert\Type(type="Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute")
     * @SpDashboardAssert\ValidAttribute
     */
    private $scopedAffiliationAttribute;

    /**
     * @var Attribute
     *
     * @Assert\Type(type="Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute")
     * @SpDashboardAssert\ValidAttribute
     */
    private $eduPersonTargetedIDAttribute;

    /**
     * @var string
     */
    private $comments;

    /**
     * @var string
     * @Assert\Choice(
     *     callback={
     *         "Surfnet\ServiceProviderDashboard\Domain\Entity\Constants",
     *         "getValidNameIdFormats"
     *     },
     *     strict=true
     * )
     */
    private $nameIdFormat = Constants::NAME_ID_FORMAT_TRANSIENT;

    /**
     * @var string
     */
    private $manageId;

    public function __construct()
    {
    }

    /**
     * @param Service $service
     * @return SaveSamlEntityCommand
     */
    public static function forCreateAction(Service $service)
    {
        $command = new self();
        $command->service = $service;
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
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    public function getService(): Service
    {
        return $this->service;
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

    public function getEnvironment(): ?string
    {
        return $this->environment;
    }

    /**
     * @param string $environment
     */
    public function setEnvironment($environment)
    {
        if (!in_array($environment, [
            Constants::ENVIRONMENT_TEST,
            Constants::ENVIRONMENT_PRODUCTION,
        ])) {
            throw new InvalidArgumentException(
                "Unknown environment '{$environment}'"
            );
        }

        $this->environment = $environment;
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

    public function getMetadataUrl(): ?string
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

    public function setAcsLocations(array $acsLocations): void
    {
        $this->acsLocations = $acsLocations;
    }

    /**
     * @return array
     */
    public function getAcsLocations(): ?array
    {
        return $this->acsLocations;
    }


    public function getEntityId(): ?string
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
    public function getCertificate(): ?string
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
    public function getLogoUrl(): ?string
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

    public function getNameNl(): ?string
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

    public function getNameEn(): ?string
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

    public function getDescriptionNl(): ?string
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

    public function getDescriptionEn(): ?string
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
    public function getApplicationUrl(): ?string
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
    public function getEulaUrl(): ?string
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

    public function getAdministrativeContact(): ?Contact
    {
        return $this->administrativeContact;
    }

    public function setAdministrativeContact(?Contact $administrativeContact)
    {
        $this->administrativeContact = $administrativeContact;
    }

    public function getTechnicalContact(): ?Contact
    {
        return $this->technicalContact;
    }

    public function setTechnicalContact(?Contact $technicalContact)
    {
        $this->technicalContact = $technicalContact;
    }

    public function getSupportContact(): ?Contact
    {
        return $this->supportContact;
    }

    public function setSupportContact(?Contact $supportContact)
    {
        $this->supportContact = $supportContact;
    }

    public function getGivenNameAttribute(): ?Attribute
    {
        return $this->givenNameAttribute;
    }

    public function setGivenNameAttribute(?Attribute $givenNameAttribute)
    {
        $this->givenNameAttribute = $givenNameAttribute;
    }

    public function getSurNameAttribute(): ?Attribute
    {
        return $this->surNameAttribute;
    }

    public function setSurNameAttribute(?Attribute $surNameAttribute)
    {
        $this->surNameAttribute = $surNameAttribute;
    }

    public function getCommonNameAttribute(): ?Attribute
    {
        return $this->commonNameAttribute;
    }

    public function setCommonNameAttribute(?Attribute $commonNameAttribute)
    {
        $this->commonNameAttribute = $commonNameAttribute;
    }

    public function getDisplayNameAttribute(): ?Attribute
    {
        return $this->displayNameAttribute;
    }

    public function setDisplayNameAttribute(?Attribute $displayNameAttribute)
    {
        $this->displayNameAttribute = $displayNameAttribute;
    }

    public function getEmailAddressAttribute(): ?Attribute
    {
        return $this->emailAddressAttribute;
    }

    public function setEmailAddressAttribute(?Attribute $emailAddressAttribute)
    {
        $this->emailAddressAttribute = $emailAddressAttribute;
    }

    public function getOrganizationAttribute(): ?Attribute
    {
        return $this->organizationAttribute;
    }

    public function setOrganizationAttribute(?Attribute $organizationAttribute)
    {
        $this->organizationAttribute = $organizationAttribute;
    }

    public function getOrganizationTypeAttribute(): ?Attribute
    {
        return $this->organizationTypeAttribute;
    }

    public function setOrganizationTypeAttribute(?Attribute $organizationTypeAttribute)
    {
        $this->organizationTypeAttribute = $organizationTypeAttribute;
    }

    public function getAffiliationAttribute(): ?Attribute
    {
        return $this->affiliationAttribute;
    }

    public function setAffiliationAttribute(?Attribute $affiliationAttribute)
    {
        $this->affiliationAttribute = $affiliationAttribute;
    }

    public function getEntitlementAttribute(): ?Attribute
    {
        return $this->entitlementAttribute;
    }

    public function setEntitlementAttribute(?Attribute $entitlementAttribute)
    {
        $this->entitlementAttribute = $entitlementAttribute;
    }

    public function getPrincipleNameAttribute(): ?Attribute
    {
        return $this->principleNameAttribute;
    }

    public function setPrincipleNameAttribute(?Attribute $principleNameAttribute)
    {
        $this->principleNameAttribute = $principleNameAttribute;
    }

    public function getUidAttribute(): ?Attribute
    {
        return $this->uidAttribute;
    }

    public function setUidAttribute(?Attribute $uidAttribute)
    {
        $this->uidAttribute = $uidAttribute;
    }

    public function getPreferredLanguageAttribute(): ?Attribute
    {
        return $this->preferredLanguageAttribute;
    }

    public function setPreferredLanguageAttribute(?Attribute $preferredLanguageAttribute)
    {
        $this->preferredLanguageAttribute = $preferredLanguageAttribute;
    }

    public function getPersonalCodeAttribute(): ?Attribute
    {
        return $this->personalCodeAttribute;
    }

    public function setPersonalCodeAttribute(?Attribute $personalCodeAttribute)
    {
        $this->personalCodeAttribute = $personalCodeAttribute;
    }

    public function getScopedAffiliationAttribute(): ?Attribute
    {
        return $this->scopedAffiliationAttribute;
    }

    public function setScopedAffiliationAttribute(?Attribute $scopedAffiliationAttribute)
    {
        $this->scopedAffiliationAttribute = $scopedAffiliationAttribute;
    }

    public function getEduPersonTargetedIDAttribute(): ?Attribute
    {
        return $this->eduPersonTargetedIDAttribute;
    }

    public function setEduPersonTargetedIDAttribute(?Attribute $eduPersonTargetedIDAttribute)
    {
        $this->eduPersonTargetedIDAttribute = $eduPersonTargetedIDAttribute;
    }

    /**
     * @return string
     */
    public function getComments(): ?string
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
    public function getNameIdFormat(): ?string
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
     * @return bool
     */
    public function hasNameIdFormat()
    {
        return !empty($this->nameIdFormat);
    }

    public function isForProduction()
    {
        return $this->environment === Constants::ENVIRONMENT_PRODUCTION;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @param Service $service
     */
    public function setService(Service $service)
    {
        $this->service = $service;
    }

    /**
     * @return string
     */
    public function getManageId()
    {
        return $this->manageId;
    }

    /**
     * @param string $manageId
     */
    public function setManageId($manageId)
    {
        $this->manageId = $manageId;
    }

    public function getProtocol(): string
    {
        return Constants::TYPE_SAML;
    }

    public function getOrganizationUnitAttribute(): ?Attribute
    {
        return $this->organizationUnitAttribute;
    }

    public function setOrganizationUnitAttribute(?Attribute $organizationUnitAttribute): void
    {
        $this->organizationUnitAttribute = $organizationUnitAttribute;
    }
}
