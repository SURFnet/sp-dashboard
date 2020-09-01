<?php

/**
 * Copyright 2019 SURFnet B.V.
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
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Contact;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\OidcGrantType;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Validator\Constraints as SpDashboardAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 *
 * @SpDashboardAssert\HasAttributes()
 */
class SaveOidcngEntityCommand implements SaveEntityCommandInterface
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
     * @var string
     *
     * @Assert\NotBlank()
     * @SpDashboardAssert\ValidClientId()
     * @SpDashboardAssert\UniqueEntityId()
     */
    private $entityId;

    /**
     * @var string
     */
    private $secret;

    /**
     * @var string[]
     * @Assert\Count(
     *      min = 1,
     *      max = 1000,
     *      minMessage = "You need to add a minimum of {{ limit }} redirect Url.|You need to add a minimum of {{ limit }} redirect Urls.",
     * )
     * @Assert\All({
     *     @Assert\NotBlank(),
     *     @SpDashboardAssert\ValidRedirectUrl()
     * })
     * @SpDashboardAssert\UniqueRedirectUrls()
     */
    private $redirectUrls;

    /**
     * @var array
     */
    private $scopes = ['openid'];

    /**
     * @var bool
     */
    private $isPublicClient;

    /**
     * @var string $grantType defaults to OidcGrantType::GRANT_TYPE_AUTHORIZATION_CODE
     *
     * @Assert\NotBlank()
     */
    private $grantType = OidcGrantType::GRANT_TYPE_AUTHORIZATION_CODE;

    /**
     * @var int
     * @Assert\NotBlank
     * @Assert\LessThanOrEqual(86400)
     * @Assert\GreaterThanOrEqual(3600)
     */
    private $accessTokenValidity = 3600;

    /**
     * @var string
     *
     * @Assert\Url()
     * @SpDashboardAssert\ValidLogo()
     * @Assert\NotBlank()
     */
    private $logoUrl;

    /**
     * The subject type is comparable to the SAML name id format, that is why the Constants::NAME_ID_FORMAT_DEFAULT
     * (transient) is used to set the default value.
     *
     * @var string
     * @Assert\Choice(
     *     callback={
     *         "Surfnet\ServiceProviderDashboard\Domain\Entity\Entity",
     *         "getValidNameIdFormats"
     *     },
     *     strict=true
     * )
     */
    private $subjectType = Constants::NAME_ID_FORMAT_TRANSIENT;

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
     * @SpDashboardAssert\ValidAttribute(groups={"production"})
     */
    private $givenNameAttribute;

    /**
     * @var Attribute
     *
     * @Assert\Type(type="Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute")
     * @SpDashboardAssert\ValidAttribute(groups={"production"})
     */
    private $surNameAttribute;

    /**
     * @var Attribute
     *
     * @Assert\Type(type="Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute")
     * @SpDashboardAssert\ValidAttribute(groups={"production"})
     */
    private $commonNameAttribute;

    /**
     * @var Attribute
     *
     * @Assert\Type(type="Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute")
     * @SpDashboardAssert\ValidAttribute(groups={"production"})
     */
    private $displayNameAttribute;

    /**
     * @var Attribute
     *
     * @Assert\Type(type="Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute")
     * @SpDashboardAssert\ValidAttribute(groups={"production"})
     */
    private $emailAddressAttribute;

    /**
     * @var Attribute
     *
     * @Assert\Type(type="Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute")
     * @SpDashboardAssert\ValidAttribute(groups={"production"})
     */
    private $organizationAttribute;

    /**
     * @var Attribute
     *
     * @Assert\Type(type="Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute")
     * @SpDashboardAssert\ValidAttribute(groups={"production"})
     */
    private $organizationTypeAttribute;

    /**
     * @var Attribute
     *
     * @Assert\Type(type="Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute")
     * @SpDashboardAssert\ValidAttribute(groups={"production"})
     */
    private $affiliationAttribute;

    /**
     * @var Attribute
     *
     * @Assert\Type(type="Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute")
     * @SpDashboardAssert\ValidAttribute(groups={"production"})
     */
    private $entitlementAttribute;

    /**
     * @var Attribute
     *
     * @Assert\Type(type="Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute")
     * @SpDashboardAssert\ValidAttribute(groups={"production"})
     */
    private $principleNameAttribute;

    /**
     * @var Attribute
     *
     * @Assert\Type(type="Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute")
     * @SpDashboardAssert\ValidAttribute(groups={"production"})
     */
    private $uidAttribute;

    /**
     * @var Attribute
     *
     * @Assert\Type(type="Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute")
     * @SpDashboardAssert\ValidAttribute(groups={"production"})
     */
    private $preferredLanguageAttribute;

    /**
     * @var Attribute
     *
     * @Assert\Type(type="Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute")
     * @SpDashboardAssert\ValidAttribute(groups={"production"})
     */
    private $personalCodeAttribute;

    /**
     * @var Attribute
     *
     * @Assert\Type(type="Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute")
     * @SpDashboardAssert\ValidAttribute(groups={"production"})
     */
    private $scopedAffiliationAttribute;

    /**
     * @var Attribute
     *
     * @Assert\Type(type="Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute")
     * @SpDashboardAssert\ValidAttribute(groups={"production"})
     */
    private $eduPersonTargetedIDAttribute;

    /**
     * @var string
     */
    private $comments;

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

    /**
     * @var bool
     */
    private $enablePlayground;

    /**
     * @var string
     */
    private $protocol = Constants::TYPE_OPENID_CONNECT_TNG;

    /**
     * @var string[]
     */
    private $resourceServers = [];

    public function __construct()
    {
    }

    /**
     * @param Service $service
     * @return SaveOidcngEntityCommand
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
     * @return SaveOidcngEntityCommand
     */
    public static function fromEntity(Entity $entity)
    {
        $command = new self();
        $command->id = $entity->getId();
        $command->status = $entity->getStatus();
        $command->manageId = $entity->getManageId();
        $command->service = $entity->getService();
        $command->archived = $entity->isArchived();
        $command->environment = $entity->getEnvironment();
        $command->entityId = $entity->getEntityId();
        $command->secret = $entity->getClientSecret();
        $command->redirectUrls = $entity->getRedirectUris();
        $command->grantType = $entity->getGrantType()->getGrantType();
        $command->logoUrl = $entity->getLogoUrl();
        $command->nameNl = $entity->getNameNl();
        $command->nameEn = $entity->getNameEn();
        // The SAML nameidformat is used as the OIDC subject type https://www.pivotaltracker.com/story/show/167511146
        $command->setSubjectType($entity->getNameIdFormat());
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
        $command->organizationNameNl = $entity->getOrganizationNameNl();
        $command->organizationNameEn = $entity->getOrganizationNameEn();
        $command->organizationDisplayNameNl = $entity->getOrganizationDisplayNameNl();
        $command->organizationDisplayNameEn = $entity->getOrganizationDisplayNameEn();
        $command->organizationUrlNl = $entity->getOrganizationUrlNl();
        $command->organizationUrlEn = $entity->getOrganizationUrlEn();
        $command->isPublicClient = $entity->isPublicClient();
        $command->accessTokenValidity = (int) $entity->getAccessTokenValidity();
        $command->enablePlayground = $entity->isEnablePlayground();
        $command->resourceServers = $entity->getOidcngResourceServers()->getResourceServers();

        if (is_array($command->resourceServers) && reset($command->resourceServers) instanceof ManageEntity) {
            $servers = $entity->getOidcngResourceServers()->getResourceServers();
            $resourceServers = [];
            foreach ($servers as $resourceServer) {
                $resourceServers[$resourceServer->getMetaData()->getEntityId()] = $resourceServer->getMetaData()->getEntityId();
            }
            $command->resourceServers = $resourceServers;
        }
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

    /**
     * @return Service
     */
    public function getService()
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
        if (!in_array(
            $environment,
            [
                Constants::ENVIRONMENT_TEST,
                Constants::ENVIRONMENT_PRODUCTION,
            ]
        )) {
            throw new InvalidArgumentException(
                "Unknown environment '{$environment}'"
            );
        }

        $this->environment = $environment;
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
    public function getClientId()
    {
        return $this->entityId;
    }

    /**
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * @param string $secret
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;
    }

    /**
     * @return string[]
     */
    public function getRedirectUrls()
    {
        if (!is_array($this->redirectUrls)) {
            return [];
        }

        return array_values($this->redirectUrls);
    }

    /**
     * @param string[] $redirectUrls
     */
    public function setRedirectUrls($redirectUrls)
    {
        $this->redirectUrls = $redirectUrls;
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
    public function getTechnicalContact(): Contact
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
     * @param Attribute $eduPersonTargetedIDAttribute
     */
    public function setEduPersonTargetedIDAttribute($eduPersonTargetedIDAttribute)
    {
        $this->eduPersonTargetedIDAttribute = $eduPersonTargetedIDAttribute;
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

    /**
     * @return string
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * @return array
     */
    public function getScopes()
    {
        return $this->scopes;
    }

    /**
     * @return bool
     */
    public function isPublicClient()
    {
        return $this->isPublicClient;
    }

    /**
     * @param bool $isPublicClient
     */
    public function setIsPublicClient($isPublicClient)
    {
        $this->isPublicClient = $isPublicClient;
    }

    /**
     * @return int
     */
    public function getAccessTokenValidity()
    {
        return $this->accessTokenValidity;
    }

    /**
     * @param int $accessTokenValidity
     */
    public function setAccessTokenValidity($accessTokenValidity)
    {
        $this->accessTokenValidity = (int) $accessTokenValidity;
    }

    /**
     * @return bool
     */
    public function isEnablePlayground()
    {
        return $this->enablePlayground;
    }

    /**
     * @param bool $enablePlayground
     */
    public function setEnablePlayground($enablePlayground)
    {
        $this->enablePlayground = $enablePlayground;
    }

    /**
     * @return OidcGrantType
     */
    public function getGrantType()
    {
        return $this->grantType;
    }

    /**
     * @param OidcGrantType $grantType
     */
    public function setGrantType($grantType)
    {
        $this->grantType = $grantType;
    }

    /**
     * @return string
     */
    public function getSubjectType()
    {
        return $this->subjectType;
    }

    /**
     * @param string $subjectType
     */
    public function setSubjectType($subjectType)
    {
        $this->subjectType = $subjectType;
        // If the SubjectType is not set in the draft, we set the default value (transient) as requested in:
        // https://www.pivotaltracker.com/story/show/167511146
        if (is_null($subjectType)) {
            $this->subjectType = Constants::NAME_ID_FORMAT_TRANSIENT;
        }
    }

    /**
     * @return string[]
     */
    public function getOidcngResourceServers()
    {
        return $this->resourceServers;
    }

    /**
     * @param string[] $resourceServers
     */
    public function setOidcngResourceServers($resourceServers)
    {
        $this->resourceServers = $resourceServers;
    }
}
