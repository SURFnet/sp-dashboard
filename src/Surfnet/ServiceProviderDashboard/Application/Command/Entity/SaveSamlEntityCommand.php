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
     *     min = 1,
     *     minMessage="At least one ACS location is required",
     *     max = 10,
     *     maxMessage = "{{ limit }} ACS locations or less are allowed"
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
     * @SpDashboardAssert\ValidAttribute(type="saml20")
     */
    private $attributes = [];

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
     * The magic getters and setters are consulted by the saml form builder.
     * Another option would be to implement a dataMapper on the
     * form or attribute container, but this might lead to needless complexity.
     */
    public function __set(string $property, ?Attribute $value)
    {
        $this->setAttribute($property, $value);
    }

    public function __get(string $property): ?Attribute
    {
        return $this->getAttribute($property);
    }

    public function setAttribute(string $property, ?Attribute $value)
    {
        $this->attributes[$property] = $value;
    }

    /**
     * The reason why a null value is returned (iso throwing an exception) is because the property accessor of
     * symfony, calling the magic getter, cannot handle exceptions.
     */
    public function getAttribute(string $property): ?Attribute
    {
        if (array_key_exists($property, $this->attributes)) {
            return $this->attributes[$property];
        }
        return null;
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
