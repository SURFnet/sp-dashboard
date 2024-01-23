<?php

//declare(strict_types = 1);

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
 */
class SaveSamlEntityCommand implements SaveEntityCommandInterface
{
    #[Assert\Uuid]
    private ?string $id = null;

    private ?string $status = null;

    private ?Service $service = null;

    /**
     * @deprecated
     */
    private bool $archived = false;

    
    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['production', 'test'], strict: true)]
    private string $environment = Constants::ENVIRONMENT_TEST;

    /**
     * Metadata URL that import last happened from.
     *
     * @deprecated
     */
    private ?string $importUrl = null;

    #[SpDashboardAssert\ValidMetadataUrl()]
    private ?string $metadataUrl = null;

    /**
     * @deprecated
     */
    private string $pastedMetadata;

    #[Assert\All([
        new Assert\NotBlank(),
        new Assert\Url(
            message: "url.notSecure",
            protocols: ["https"]
        ),
    ])]
    #[Assert\Count(min: 1, max: 10, minMessage: 'At least one ACS location is required', maxMessage: '{{ limit }} ACS locations or less are allowed')]
    private ?array $acsLocations = null;

    #[SpDashboardAssert\ValidEntityId()]
    #[SpDashboardAssert\UniqueEntityId()]
    #[Assert\NotBlank]
    private string $entityId;

    #[SpDashboardAssert\ValidSSLCertificate()]
    private string $certificate;

    #[SpDashboardAssert\ValidLogo()]
    #[Assert\Url]
    #[Assert\NotBlank]
    private ?string $logoUrl = null;

    #[Assert\NotBlank]
    private ?string $nameNl = null;

    #[Assert\NotBlank]
    private ?string $nameEn = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 300)]
    private ?string $descriptionNl = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 300)]
    private ?string $descriptionEn = null;

    #[Assert\Url]
    private ?string $applicationUrl = null;

    #[Assert\Url]
    private ?string $eulaUrl = null;

    
    #[Assert\Type(type: Contact::class)]
    #[Assert\Valid(groups: ['production'])]
    private ?Contact $administrativeContact = null;

    
    #[Assert\Type(type: Contact::class)]
    #[Assert\Valid]
    private ?Contact $technicalContact = null;

    
    #[Assert\Type(type: Contact::class)]
    #[Assert\Valid(groups: ['production'])]
    private ?Contact $supportContact = null;

    #[SpDashboardAssert\ValidAttribute(
        type: 'saml20'
    )]
    private array $attributes = [];

    private ?string $comments = null;

    #[Assert\Choice(callback: [Constants::class, 'getValidNameIdFormats'], strict: true)]
    private ?string $nameIdFormat = Constants::NAME_ID_FORMAT_TRANSIENT;

    private ?string $manageId = null;
    private ?Attribute $organizationUnitAttribute = null;

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

    public function setAttribute(string $property, ?Attribute $value): void
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

    public static function forCreateAction(Service $service): self
    {
        $command = new self();
        $command->service = $service;
        return $command;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function getService(): Service
    {
        return $this->service;
    }

    public function isArchived(): bool
    {
        return $this->archived;
    }

    public function setArchived(bool $archived): void
    {
        $this->archived = $archived;
    }

    public function getEnvironment(): ?string
    {
        return $this->environment;
    }

    public function setEnvironment(string $environment): void
    {
        if (!in_array(
            $environment,
            [
            Constants::ENVIRONMENT_TEST,
            Constants::ENVIRONMENT_PRODUCTION,
            ]
        )
        ) {
            throw new InvalidArgumentException(
                "Unknown environment '{$environment}'"
            );
        }

        $this->environment = $environment;
    }

    public function getImportUrl(): string
    {
        return $this->importUrl;
    }

    public function setImportUrl(?string $importUrl): void
    {
        $this->importUrl = $importUrl;
    }

    public function getMetadataUrl(): ?string
    {
        return $this->metadataUrl;
    }

    public function setMetadataUrl(string $metadataUrl): void
    {
        $this->metadataUrl = $metadataUrl;
    }

    public function getPastedMetadata(): string
    {
        return $this->pastedMetadata;
    }

    public function setPastedMetadata(string $pastedMetadata): void
    {
        $this->pastedMetadata = $pastedMetadata;
    }

    public function setAcsLocations(array $acsLocations): void
    {
        $this->acsLocations = $acsLocations;
    }

    public function getAcsLocations(): ?array
    {
        return $this->acsLocations;
    }


    public function getEntityId(): ?string
    {
        return $this->entityId;
    }

    public function setEntityId(string $entityId): void
    {
        $this->entityId = $entityId;
    }

    public function getCertificate(): ?string
    {
        return $this->certificate;
    }

    public function setCertificate(string $certificate): void
    {
        $this->certificate = $certificate;
    }

    public function getLogoUrl(): ?string
    {
        return $this->logoUrl;
    }

    public function setLogoUrl(string $logoUrl): void
    {
        $this->logoUrl = $logoUrl;
    }

    public function getNameNl(): ?string
    {
        return $this->nameNl;
    }

    public function setNameNl(string $nameNl): void
    {
        $this->nameNl = $nameNl;
    }

    public function getNameEn(): ?string
    {
        return $this->nameEn;
    }

    public function setNameEn(string $nameEn): void
    {
        $this->nameEn = $nameEn;
    }

    public function getDescriptionNl(): ?string
    {
        return $this->descriptionNl;
    }

    public function setDescriptionNl(string $descriptionNl): void
    {
        $this->descriptionNl = $descriptionNl;
    }

    public function getDescriptionEn(): ?string
    {
        return $this->descriptionEn;
    }

    public function setDescriptionEn(string $descriptionEn): void
    {
        $this->descriptionEn = $descriptionEn;
    }

    public function getApplicationUrl(): ?string
    {
        return $this->applicationUrl;
    }

    public function setApplicationUrl(?string $applicationUrl): void
    {
        $this->applicationUrl = $applicationUrl;
    }

    public function getEulaUrl(): ?string
    {
        return $this->eulaUrl;
    }

    public function setEulaUrl(?string $eulaUrl): void
    {
        $this->eulaUrl = $eulaUrl;
    }

    public function getAdministrativeContact(): ?Contact
    {
        return $this->administrativeContact;
    }

    public function setAdministrativeContact(?Contact $administrativeContact): void
    {
        $this->administrativeContact = $administrativeContact;
    }

    public function getTechnicalContact(): ?Contact
    {
        return $this->technicalContact;
    }

    public function setTechnicalContact(?Contact $technicalContact): void
    {
        $this->technicalContact = $technicalContact;
    }

    public function getSupportContact(): ?Contact
    {
        return $this->supportContact;
    }

    public function setSupportContact(?Contact $supportContact): void
    {
        $this->supportContact = $supportContact;
    }

    public function getComments(): ?string
    {
        return $this->comments;
    }

    public function setComments(string $comments): void
    {
        $this->comments = $comments;
    }

    public function getNameIdFormat(): ?string
    {
        return $this->nameIdFormat;
    }

    public function setNameIdFormat(?string $nameIdFormat): void
    {
        $this->nameIdFormat = $nameIdFormat;
    }

    public function hasNameIdFormat(): bool
    {
        return $this->nameIdFormat !== '' && $this->nameIdFormat !== '0';
    }

    public function isForProduction(): bool
    {
        return $this->environment === Constants::ENVIRONMENT_PRODUCTION;
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

    public function setService(Service $service): void
    {
        $this->service = $service;
    }

    public function getManageId(): ?string
    {
        return $this->manageId;
    }

    public function setManageId(?string $manageId): void
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
