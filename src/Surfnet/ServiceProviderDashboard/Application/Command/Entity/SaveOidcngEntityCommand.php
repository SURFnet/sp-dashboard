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
     * @var bool
     */
    private $isPublicClient;

    /**
     * @var array $grants defaults to Constants::GRANT_TYPE_AUTHORIZATION_CODE
     *
     * @Assert\NotBlank()
     */
    private $grants = [Constants::GRANT_TYPE_AUTHORIZATION_CODE];

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
     *         "Surfnet\ServiceProviderDashboard\Domain\Entity\Constants",
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

    private $attributes = [];
    /**
     * @var string
     */
    private $comments;

    /**
     * @var string
     */
    private $manageId;

    /**
     * @var bool
     */
    private $enablePlayground;

    /**
     * @var string[]
     */
    private $resourceServers = [];

    /** @var bool */
    private $isCopy;

    public function __construct()
    {
    }

    public function __set(string $property, ?Attribute $value)
    {
        $this->attributes[$property] = $value;
    }

    public function __get(string $property): ?Attribute
    {
        if (array_key_exists($property, $this->attributes)) {
            return $this->attributes[$property];
        }
        return null;
    }

    public function setAttribute(string $property, ?Attribute $value)
    {
        $this->__set($property, $value);
    }

    public function getAttribute(string $property): ?Attribute
    {
        return $this->__get($property);
    }
    /**
     * @param Service $service
     * @param bool $isCopy
     * @return SaveOidcngEntityCommand
     */
    public static function forCreateAction(Service $service, bool $isCopy = false)
    {
        $command = new self();
        $command->service = $service;
        $command->isCopy = $isCopy;
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
    public function isCopy()
    {
        return $this->isCopy;
    }

    /**
     * @param bool $isCopy
     */
    public function setIsCopy(bool $isCopy)
    {
        $this->isCopy = $isCopy;
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

    public function getEntityId(): ?string
    {
        return $this->entityId;
    }

    /**
     * @param string $entityId
     */
    public function setEntityId($entityId)
    {
        $this->entityId = strtolower($entityId);
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
        return $this->redirectUrls;
    }

    /**
     * @param string[] $redirectUrls
     */
    public function setRedirectUrls($redirectUrls)
    {
        $urls = []; // because numeric array keys can be sparse so a for-loop cannot be trusted
        foreach ($redirectUrls as $url) {
            $protocolSlashes = strpos($url, '://');
            $hostname = strpos($url, '/', $protocolSlashes + 3);
            $lowercased = strtolower(substr($url, 0, $hostname));
            $urls[] = $lowercased . substr($url, $hostname);
        }
        $this->redirectUrls = $urls;
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

    /**
     * @param Contact $administrativeContact
     */
    public function setAdministrativeContact($administrativeContact)
    {
        $this->administrativeContact = $administrativeContact;
    }

    public function getTechnicalContact(): ?Contact
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

    public function getSupportContact(): ?Contact
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
        return Constants::TYPE_OPENID_CONNECT_TNG;
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
    public function getGrants()
    {
        return $this->grants;
    }

    /**
     * @param OidcGrantType $grants
     */
    public function setGrants($grants)
    {
        $this->grants = $grants;
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

    public function getMetadataUrl(): ?string
    {
        return null;
    }

    public function getCertificate(): ?string
    {
        return null;
    }

    public function getNameIdFormat(): string
    {
        return $this->getSubjectType();
    }

    public function getAcsLocations(): ?array
    {
        return null;
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
