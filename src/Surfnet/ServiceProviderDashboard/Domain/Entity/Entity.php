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
use Gedmo\Mapping\Annotation as Gedmo;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Contact as ContactPerson;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Dto\AttributeList;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Dto\ManageEntity;
use Surfnet\ServiceProviderDashboard\Legacy\Repository\AttributesMetadataRepository;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @package Surfnet\ServiceProviderDashboard\Entity
 *
 * @ORM\Entity(repositoryClass="Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Repository\EntityRepository")
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class Entity
{
    const BINDING_HTTP_POST = 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST';

    // When adding valid name id formats, don't forget to add them to self::getValidNameIdFormats()
    const NAME_ID_FORMAT_DEFAULT = 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient';
    const NAME_ID_FORMAT_PERSISTENT = 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent';
    const NAME_ID_FORMAT_UNSPECIFIED = 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified';

    const ENVIRONMENT_TEST = 'test';
    const ENVIRONMENT_PRODUCTION = 'production';

    const STATE_DRAFT = 'draft';
    const STATE_PUBLISHED = 'published';
    const STATE_PUBLICATION_REQUESTED = 'requested';
    const STATE_REMOVAL_REQUESTED = 'removal requested';

    const TYPE_SAML = 'saml20';
    const TYPE_OPENID_CONNECT = 'oidc';

    const GRANT_TYPE_AUTHORIZATION_CODE_CODE = 'authorization_code_code';
    const GRANT_TYPE_IMPLICIT_ID_TOKEN_TOKEN = 'implicit_id_token_token';
    const GRANT_TYPE_IMPLICIT_ID_TOKEN = 'implicit_id_token';

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
    private $environment = self::ENVIRONMENT_TEST;


    /**
     * @var string
     * @ORM\Column(type="string")
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
     * @ORM\Column(type="string", nullable=true)
     */
    private $ticketNo;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $manageId;

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
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $pastedMetadata;

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
    private $nameIdFormat;

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
    private $organizationNameEn;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $organizationDisplayNameEn;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $organizationUrlEn;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $organizationNameNl;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $organizationDisplayNameNl;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $organizationUrlNl;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $comments;

    /**
     * @var Service
     *
     * @ORM\ManyToOne(targetEntity="Service", inversedBy="entities")
     * @ORM\JoinColumn(nullable=false)
     */
    private $service;

    /**
     * @param ManageEntity $manageEntity
     * @param string $environment
     * @param int $serviceId
     * @return Entity
     */
    public static function fromManageResponse(ManageEntity $manageEntity, $environment, $serviceId)
    {
        $metaData = $manageEntity->getMetaData();
        $coin = $metaData->getCoin();
        $arp = $manageEntity->getAttributes();

        $entity = new self();

        $entity->setEnvironment($environment);
        $entity->setStatus($manageEntity->getStatus());
        $entity->setManageId($manageEntity->getId());
        $entity->setEntityId($metaData->getEntityId());
        $entity->setMetadataUrl($metaData->getMetaDataUrl());
        $entity->setAcsLocation($metaData->getAcsLocation());
        $entity->setNameIdFormat($metaData->getNameIdFormat());
        $entity->setCertificate($metaData->getCertData());
        $entity->setDescriptionEn($metaData->getDescriptionEn());
        $entity->setDescriptionNl($metaData->getDescriptionNl());
        $entity->setNameEn($metaData->getNameEn());
        $entity->setNameNl($metaData->getNameNl());
        $entity->setLogoUrl($metaData->getLogo()->getUrl());
        $entity->setOrganizationDisplayNameEn($metaData->getOrganization()->getDisplayNameEn());
        $entity->setOrganizationDisplayNameNl($metaData->getOrganization()->getDisplayNameNl());
        $entity->setOrganizationNameEn($metaData->getOrganization()->getNameEn());
        $entity->setOrganizationNameNl($metaData->getOrganization()->getNameNl());
        $entity->setOrganizationUrlEn($metaData->getOrganization()->getUrlEn());
        $entity->setOrganizationUrlNl($metaData->getOrganization()->getUrlNl());
        $entity->setApplicationUrl($coin->getApplicationUrl());
        $entity->setEulaUrl($coin->getEula());

        $administrative = $metaData->getContacts()->findAdministrativeContact();
        if ($administrative) {
            $contact = new ContactPerson();
            $contact->setFirstName($administrative->getGivenName());
            $contact->setLastName($administrative->getSurName());
            $contact->setEmail($administrative->getEmail());
            $contact->setPhone($administrative->getPhone());
            $entity->setAdministrativeContact($contact);
        }

        $technical = $metaData->getContacts()->findTechnicalContact();
        if ($technical) {
            $contact = new ContactPerson();
            $contact->setFirstName($technical->getGivenName());
            $contact->setLastName($technical->getSurName());
            $contact->setEmail($technical->getEmail());
            $contact->setPhone($technical->getPhone());
            $entity->setTechnicalContact($contact);
        }

        $support = $metaData->getContacts()->findSupportContact();
        if ($support) {
            $contact = new ContactPerson();
            $contact->setFirstName($support->getGivenName());
            $contact->setLastName($support->getSurName());
            $contact->setEmail($support->getEmail());
            $contact->setPhone($support->getPhone());
            $entity->setSupportContact($contact);
        }

        self::setAttributesOn($entity, $arp);

        $service = new Service();
        $service->setId($serviceId);
        $entity->setService($service);

        return $entity;
    }

    private static function setAttributesOn($entity, AttributeList $attributeList)
    {
        $attributeRepository = new AttributesMetadataRepository('../app/Resources');
        // Copy the ARP attributes to the new entity based on the data from manage.
        foreach ($attributeRepository->findAll() as $attributeDefinition) {
            $urn = reset($attributeDefinition->urns);
            $manageAttribute = $attributeList->findByUrn($urn);
            if (!$manageAttribute) {
                continue;
            }

            $setter = $attributeDefinition->setterName;
            if (empty($setter)) {
                continue;
            }

            $attribute = new Attribute();
            $attribute->setRequested(true);
            $attribute->setMotivation($manageAttribute->getMotivation());

            $entity->{$setter}($attribute);
        }
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param Service $service
     */
    public function setService($service)
    {
        $this->service = $service;
    }

    /**
     * @param string $ticketNumber
     */
    public function setTicketNumber($ticketNumber)
    {
        $this->ticketNo = $ticketNumber;
    }

    /**
     * @param bool $archived
     */
    public function setArchived($archived)
    {
        $this->archived = (bool) $archived;
    }

    /**
     * @param string $environment
     */
    public function setEnvironment($environment)
    {
        $this->environment = $environment;
    }

    /**
     * @param string $manageId
     */
    public function setManageId($manageId)
    {
        $this->manageId = $manageId;
    }

    /**
     * @param string $importUrl
     */
    public function setImportUrl($importUrl)
    {
        $this->importUrl = $importUrl;
    }

    /**
     * @param string $metadataUrl
     */
    public function setMetadataUrl($metadataUrl)
    {
        $this->metadataUrl = $metadataUrl;
    }

    /**
     * @param string $metadataXml
     */
    public function setMetadataXml($metadataXml)
    {
        $this->metadataXml = $metadataXml;
    }

    /**
     * @param string $pastedMetadata
     */
    public function setPastedMetadata($pastedMetadata)
    {
        $this->pastedMetadata = $pastedMetadata;
    }

    /**
     * @param string $acsLocation
     */
    public function setAcsLocation($acsLocation)
    {
        $this->acsLocation = $acsLocation;
    }

    /**
     * @param string $entityId
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;
    }

    /**
     * @param string $certificate
     */
    public function setCertificate($certificate)
    {
        $this->certificate = $certificate;
    }

    /**
     * @param string $logoUrl
     */
    public function setLogoUrl($logoUrl)
    {
        $this->logoUrl = $logoUrl;
    }

    /**
     * @param string $nameNl
     */
    public function setNameNl($nameNl)
    {
        $this->nameNl = $nameNl;
    }

    /**
     * @param string $nameEn
     */
    public function setNameEn($nameEn)
    {
        $this->nameEn = $nameEn;
    }

    /**
     * @param string $descriptionNl
     */
    public function setDescriptionNl($descriptionNl)
    {
        $this->descriptionNl = $descriptionNl;
    }

    /**
     * @param string $descriptionEn
     */
    public function setDescriptionEn($descriptionEn)
    {
        $this->descriptionEn = $descriptionEn;
    }

    /**
     * @param string $applicationUrl
     */
    public function setApplicationUrl($applicationUrl)
    {
        $this->applicationUrl = $applicationUrl;
    }

    /**
     * @param string $eulaUrl
     */
    public function setEulaUrl($eulaUrl)
    {
        $this->eulaUrl = $eulaUrl;
    }

    /**
     * @param ContactPerson $administrativeContact
     */
    public function setAdministrativeContact(ContactPerson $administrativeContact)
    {
        $this->administrativeContact = $administrativeContact;
    }

    /**
     * @param ContactPerson $technicalContact
     */
    public function setTechnicalContact(ContactPerson $technicalContact)
    {
        $this->technicalContact = $technicalContact;
    }

    /**
     * @param ContactPerson $supportContact
     */
    public function setSupportContact(ContactPerson $supportContact)
    {
        $this->supportContact = $supportContact;
    }

    /**
     * @param Attribute $givenNameAttribute
     */
    public function setGivenNameAttribute(Attribute $givenNameAttribute)
    {
        $this->givenNameAttribute = $givenNameAttribute;
    }

    /**
     * @param Attribute $surNameAttribute
     */
    public function setSurNameAttribute(Attribute $surNameAttribute)
    {
        $this->surNameAttribute = $surNameAttribute;
    }

    /**
     * @param Attribute $commonNameAttribute
     */
    public function setCommonNameAttribute(Attribute $commonNameAttribute)
    {
        $this->commonNameAttribute = $commonNameAttribute;
    }

    /**
     * @param Attribute $displayNameAttribute
     */
    public function setDisplayNameAttribute(Attribute $displayNameAttribute)
    {
        $this->displayNameAttribute = $displayNameAttribute;
    }

    /**
     * @param Attribute $emailAddressAttribute
     */
    public function setEmailAddressAttribute(Attribute $emailAddressAttribute)
    {
        $this->emailAddressAttribute = $emailAddressAttribute;
    }

    /**
     * @param Attribute $organizationAttribute
     */
    public function setOrganizationAttribute(Attribute $organizationAttribute)
    {
        $this->organizationAttribute = $organizationAttribute;
    }

    /**
     * @param Attribute $organizationTypeAttribute
     */
    public function setOrganizationTypeAttribute(Attribute $organizationTypeAttribute)
    {
        $this->organizationTypeAttribute = $organizationTypeAttribute;
    }

    /**
     * @param Attribute $affiliationAttribute
     */
    public function setAffiliationAttribute(Attribute $affiliationAttribute)
    {
        $this->affiliationAttribute = $affiliationAttribute;
    }

    /**
     * @param Attribute $entitlementAttribute
     */
    public function setEntitlementAttribute(Attribute $entitlementAttribute)
    {
        $this->entitlementAttribute = $entitlementAttribute;
    }

    /**
     * @param Attribute $principleNameAttribute
     */
    public function setPrincipleNameAttribute(Attribute $principleNameAttribute)
    {
        $this->principleNameAttribute = $principleNameAttribute;
    }

    /**
     * @param Attribute $uidAttribute
     */
    public function setUidAttribute(Attribute $uidAttribute)
    {
        $this->uidAttribute = $uidAttribute;
    }

    /**
     * @param Attribute $preferredLanguageAttribute
     */
    public function setPreferredLanguageAttribute(Attribute $preferredLanguageAttribute)
    {
        $this->preferredLanguageAttribute = $preferredLanguageAttribute;
    }

    /**
     * @param Attribute $personalCodeAttribute
     */
    public function setPersonalCodeAttribute(Attribute $personalCodeAttribute)
    {
        $this->personalCodeAttribute = $personalCodeAttribute;
    }

    /**
     * @param Attribute $scopedAffiliationAttribute
     */
    public function setScopedAffiliationAttribute(Attribute $scopedAffiliationAttribute)
    {
        $this->scopedAffiliationAttribute = $scopedAffiliationAttribute;
    }

    /**
     * @param Attribute $eduPersonTargetedIDAttribute
     */
    public function setEduPersonTargetedIDAttribute(Attribute $eduPersonTargetedIDAttribute)
    {
        $this->eduPersonTargetedIDAttribute = $eduPersonTargetedIDAttribute;
    }

    /**
     * @param string $comments
     */
    public function setComments($comments)
    {
        $this->comments = $comments;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
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
    public function getManageId()
    {
        return $this->manageId;
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
    public function getPastedMetadata()
    {
        return $this->pastedMetadata;
    }

    /**
     * @return string
     */
    public function getAcsLocation()
    {
        return $this->acsLocation;
    }

    /**
     * The binding of the ACS URL is always POST.
     *
     * When importing XML metadata (Legacy\Metadata\Parser) the dashboard only
     * imports the POST ACS URL. Other formats are not supported by manage or
     * the dashboard.
     *
     * @return string
     */
    public function getAcsBinding()
    {
        return self::BINDING_HTTP_POST;
    }

    /**
     * @return string
     */
    public function getNameIdFormat()
    {
        return $this->nameIdFormat;
    }

    /**
     * @param string $format
     * @return Entity
     */
    public function setNameIdFormat($format)
    {
        $this->nameIdFormat = $format;

        return $this;
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
     * @return ContactPerson|null
     */
    public function getAdministrativeContact()
    {
        if (!is_null($this->administrativeContact) && !$this->administrativeContact->isContactSet()) {
            return null;
        }
        return $this->administrativeContact;
    }

    /**
     * @return ContactPerson|null
     */
    public function getTechnicalContact()
    {
        if (!is_null($this->technicalContact) && !$this->technicalContact->isContactSet()) {
            return null;
        }
        return $this->technicalContact;
    }

    /**
     * @return ContactPerson|null
     */
    public function getSupportContact()
    {
        if (!is_null($this->supportContact) && !$this->supportContact->isContactSet()) {
            return null;
        }
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

    /**
     * @return bool
     */
    public function hasComments()
    {
        return !(empty($this->comments));
    }

    /**
     * @return string
     */
    public function getOrganizationNameEn()
    {
        return $this->organizationNameEn;
    }

    /**
     * @param string $value
     * @return Entity
     */
    public function setOrganizationNameEn($value)
    {
        $this->organizationNameEn = (string) $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getOrganizationDisplayNameEn()
    {
        return $this->organizationDisplayNameEn;
    }

    /**
     * @param string $value
     * @return Entity
     */
    public function setOrganizationDisplayNameEn($value)
    {
        $this->organizationDisplayNameEn = (string) $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getOrganizationUrlEn()
    {
        return $this->organizationUrlEn;
    }

    /**
     * @param string $value
     * @return Entity
     */
    public function setOrganizationUrlEn($value)
    {
        $this->organizationUrlEn = (string) $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getOrganizationNameNl()
    {
        return $this->organizationNameNl;
    }

    /**
     * @param string $value
     * @return Entity
     */
    public function setOrganizationNameNl($value)
    {
        $this->organizationNameNl = (string) $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getOrganizationDisplayNameNl()
    {
        return $this->organizationDisplayNameNl;
    }

    /**
     * @param string $value
     * @return Entity
     */
    public function setOrganizationDisplayNameNl($value)
    {
        $this->organizationDisplayNameNl = (string) $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getOrganizationUrlNl()
    {
        return $this->organizationUrlNl;
    }

    /**
     * @param string $value
     * @return Entity
     */
    public function setOrganizationUrlNl($value)
    {
        $this->organizationUrlNl = (string) $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    public function isDraft()
    {
        return ($this->status == self::STATE_DRAFT);
    }

    public function isPublished()
    {
        return ($this->status == self::STATE_PUBLISHED);
    }

    public function isProduction()
    {
        return ($this->environment == self::ENVIRONMENT_PRODUCTION);
    }

    public static function getValidNameIdFormats()
    {
        return [
            static::NAME_ID_FORMAT_DEFAULT,
            static::NAME_ID_FORMAT_PERSISTENT,
        ];
    }
}
