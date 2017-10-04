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

namespace Surfnet\ServiceProviderDashboard\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use Surfnet\ServiceProviderDashboard\Domain\Model\Contact as ContactPerson;
use Surfnet\ServiceProviderDashboard\Domain\Model\Attribute;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @package Surfnet\ServiceProviderDashboard\Entity
 *
 * @ORM\Entity(repositoryClass="Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Repository\ServiceRepository")
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Service
{
    const STATE_DRAFT = 0;
    const STATE_PUBLISHED = 1;
    const STATE_FINISHED = 2;
    const ENVIRONMENT_CONNECT = 'connect';
    const ENVIRONMENT_PRODUCTION = 'production';

    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(type="guid", unique=true, length=36)
     */
    private $id;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $archived = false;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $environment = self::ENVIRONMENT_CONNECT;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $status = self::STATE_DRAFT;

    /**
     * @var \DateTime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @var \DateTime $updated
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updated;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $ticketNo;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $janusId;

    /**
     * Metadata URL that import last happened from.
     *
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $importUrl;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $metadataUrl;

    /**
     * SAML XML Metadata for entity.
     *
     * Imported from metadataurl.
     *
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $metadataXml;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $acsLocation;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $entityId;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $certificate;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $logoUrl;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $nameNl;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $nameEn;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $descriptionNl;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $descriptionEn;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $applicationUrl;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $eulaUrl;

    /**
     * @var ContactPerson
     * @ORM\Column(type="object", nullable=true)
     */
    private $administrativeContact;

    /**
     * @var ContactPerson
     * @ORM\Column(type="object", nullable=true)
     */
    private $technicalContact;

    /**
     * @var ContactPerson
     * @ORM\Column(type="object", nullable=true)
     */
    private $supportContact;

    /**
     * @var Attribute
     * @ORM\Column(type="object", nullable=true)
     */
    private $givenNameAttribute;

    /**
     * @var Attribute
     * @ORM\Column(type="object", nullable=true)
     */
    private $surNameAttribute;

    /**
     * @var Attribute
     * @ORM\Column(type="object", nullable=true)
     */
    private $commonNameAttribute;

    /**
     * @var Attribute
     * @ORM\Column(type="object", nullable=true)
     */
    private $displayNameAttribute;

    /**
     * @var Attribute
     * @ORM\Column(type="object", nullable=true)
     */
    private $emailAddressAttribute;

    /**
     * @var Attribute
     * @ORM\Column(type="object", nullable=true)
     */
    private $organizationAttribute;

    /**
     * @var Attribute
     * @ORM\Column(type="object", nullable=true)
     */
    private $organizationTypeAttribute;

    /**
     * @var Attribute
     * @ORM\Column(type="object", nullable=true)
     */
    private $affiliationAttribute;

    /**
     * @var Attribute
     * @ORM\Column(type="object", nullable=true)
     * @Assert\Type(type="AppBundle\Model\Attribute")
     * @Assert\Valid()
     */
    private $entitlementAttribute;

    /**
     * @var Attribute
     * @ORM\Column(type="object", nullable=true)
     */
    private $principleNameAttribute;

    /**
     * @var Attribute
     * @ORM\Column(type="object", nullable=true)
     */
    private $uidAttribute;

    /**
     * @var Attribute
     * @ORM\Column(type="object", nullable=true)
     */
    private $preferredLanguageAttribute;

    /**
     * @var Attribute
     * @ORM\Column(type="object", nullable=true)
     */
    private $personalCodeAttribute;

    /**
     * @var Attribute
     * @ORM\Column(type="object", nullable=true)
     */
    private $scopedAffiliationAttribute;

    /**
     * @var Attribute
     * @ORM\Column(type="object", nullable=true)
     */
    private $eduPersonTargetedIDAttribute;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $comments;

    /**
     * @var Supplier
     *
     * @ORM\ManyToOne(targetEntity="Supplier", inversedBy="services")
     * @ORM\JoinColumn(nullable=false)
     */
    private $supplier;

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param Supplier $supplier
     */
    public function setSupplier($supplier)
    {
        $this->supplier = $supplier;
    }

    /**
     * @param string $ticketNumber
     */
    public function setTicketNumber($ticketNumber)
    {
        $this->ticketNo = $ticketNumber;
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
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTicketNumber()
    {
        return $this->ticketNo;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @return bool
     */
    public function isArchived()
    {
        return $this->archived;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @return string
     */
    public function getTicketNo()
    {
        return $this->ticketNo;
    }

    /**
     * @return string
     */
    public function getJanusId()
    {
        return $this->janusId;
    }

    /**
     * @return string
     */
    public function getImportUrl()
    {
        return $this->importUrl;
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
    public function getMetadataXml()
    {
        return $this->metadataXml;
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
     * @return ContactPerson
     */
    public function getAdministrativeContact()
    {
        return $this->administrativeContact;
    }

    /**
     * @return ContactPerson
     */
    public function getTechnicalContact()
    {
        return $this->technicalContact;
    }

    /**
     * @return ContactPerson
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
    public function getComments()
    {
        return $this->comments;
    }
}
