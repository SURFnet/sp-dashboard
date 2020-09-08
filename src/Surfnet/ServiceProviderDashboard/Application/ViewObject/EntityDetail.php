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

use Surfnet\ServiceProviderDashboard\Application\Parser\OidcClientIdParser;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\AttributeList;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Contact;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class EntityDetail
{
    /**
     * The local storage id
     * @var string
     */
    private $id;

    /**
     * The manage id
     * @var string
     */
    private $manageId;

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
    private $protocol;

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
     * @var EntityActions
     */
    private $actions;

    /**
     * @var string[]
     */
    private $redirectUris;

    /**
     * @var string
     */
    private $grantType;

    /**
     * @var bool
     */
    private $playgroundEnabled;

    /**
     * @var int
     */
    private $accessTokenValidity;

    /**
     * @var bool
     */
    private $isPublicClient;

    /**
     * @var ManageEntity[]|null
     */
    private $resourceServers = null;

    private function __construct()
    {
    }

    /**
     * @param ManageEntity $entity
     *
     * @return EntityDetail
     */
    public static function fromEntity(ManageEntity $entity)
    {
        $entityDetail = new self();
        $entityDetail->id = $entity->getId();
        $entityDetail->manageId = $entity->getId();
        $entityDetail->protocol = $entity->getProtocol()->getProtocol();
        if ($entity->getProtocol() == Constants::TYPE_OPENID_CONNECT) {
            $entityDetail->grantType = $entity->getOidcClient()->getGrantType();
            $entityDetail->redirectUris = $entity->getOidcClient()->getRedirectUris();
            $entityDetail->playgroundEnabled = $entity->getOidcClient()->isPlaygroundEnabled();
        }

        if ($entity->getProtocol() == Constants::TYPE_OPENID_CONNECT_TNG) {
            $entityDetail->grantType = $entity->getOidcClient()->getGrantType();
            $entityDetail->isPublicClient = $entity->getOidcClient()->isPublicClient();
            $entityDetail->accessTokenValidity = $entity->getOidcClient()->getAccessTokenValidity();
            $entityDetail->redirectUris = $entity->getOidcClient()->getRedirectUris();
            $entityDetail->playgroundEnabled = $entity->getOidcClient()->isPlaygroundEnabled();
            $entityDetail->resourceServers = $entity->getOidcClient()->getResourceServers();
        }

        $entityDetail->metadataUrl = $entity->getMetaData()->getMetaDataUrl();
        $entityDetail->acsLocation = $entity->getMetaData()->getAcsLocation();
        $entityDetail->entityId = $entity->getMetaData()->getEntityId();
        $entityDetail->certificate = $entity->getMetaData()->getCertData();
        $entityDetail->logoUrl = $entity->getMetaData()->getLogo()->getUrl();
        $entityDetail->nameNl = $entity->getMetaData()->getNameNl();
        $entityDetail->nameEn = $entity->getMetaData()->getNameEn();
        $entityDetail->descriptionNl = $entity->getMetaData()->getDescriptionEn();
        $entityDetail->descriptionEn = $entity->getMetaData()->getDescriptionNl();
        $entityDetail->applicationUrl = $entity->getMetaData()->getCoin()->getApplicationUrl();
        $entityDetail->eulaUrl = $entity->getMetaData()->getCoin()->getEula();
        $entityDetail->administrativeContact = $entity->getMetaData()->getContacts()->findAdministrativeContact();
        $entityDetail->technicalContact = $entity->getMetaData()->getContacts()->findTechnicalContact();
        $entityDetail->supportContact = $entity->getMetaData()->getContacts()->findSupportContact();
        $entityDetail->nameIdFormat = $entity->getMetaData()->getNameIdFormat();
        $entityDetail->organizationNameNl = $entity->getMetaData()->getOrganization()->getNameNl();
        $entityDetail->organizationNameEn = $entity->getMetaData()->getOrganization()->getNameEn();
        $entityDetail->organizationDisplayNameNl = $entity->getMetaData()->getOrganization()->getDisplayNameNl();
        $entityDetail->organizationDisplayNameEn = $entity->getMetaData()->getOrganization()->getDisplayNameEn();
        $entityDetail->organizationUrlNl = $entity->getMetaData()->getOrganization()->getUrlNl();
        $entityDetail->organizationUrlEn = $entity->getMetaData()->getOrganization()->getUrlEn();

        self::setAttributes($entityDetail, $entity->getAttributes());

        $actionId = $entityDetail->manageId;
        if ($entityDetail->isLocalEntity()) {
            $actionId = $entityDetail->id;
        }
        $entityDetail->actions = new EntityActions(
            $actionId,
            $entity->getService()->getId(),
            $entity->getStatus(),
            $entity->getEnvironment(),
            $entity->getProtocol(),
            $entity->isReadOnly()
        );
        return $entityDetail;
    }

    public function isLocalEntity()
    {
        return !is_null($this->id);
    }

    private static function setAttributes(EntityDetail $entityDetail, AttributeList $attributes)
    {
        $entityDetail->givenNameAttribute = $attributes->findByUrn('urn:mace:dir:attribute-def:givenName');
        $entityDetail->surNameAttribute = $attributes->findByUrn('urn:mace:dir:attribute-def:surName');
        $entityDetail->commonNameAttribute = $attributes->findByUrn('urn:mace:dir:attribute-def:cn');
        $entityDetail->displayNameAttribute = $attributes->findByUrn('urn:mace:dir:attribute-def:displayName');
        $entityDetail->emailAddressAttribute = $attributes->findByUrn('urn:mace:dir:attribute-def:mail');
        $entityDetail->organizationAttribute = $attributes->findByUrn('urn:mace:terena:attribute-def:schacHomeOrganization');
        $entityDetail->organizationTypeAttribute = $attributes->findByUrn('urn:mace:terena:attribute-def:schacHomeOrganizationType');
        $entityDetail->affiliationAttribute = $attributes->findByUrn('urn:mace:dir:attribute-def:eduPersonAffiliation');
        $entityDetail->entitlementAttribute = $attributes->findByUrn('urn:mace:dir:attribute-def:eduPersonEntitlement');
        $entityDetail->principleNameAttribute = $attributes->findByUrn('urn:mace:dir:attribute-def:eduPersonPrincipalName');
        $entityDetail->uidAttribute = $attributes->findByUrn('urn:mace:dir:attribute-def:uid');
        $entityDetail->preferredLanguageAttribute = $attributes->findByUrn('urn:mace:dir:attribute-def:preferredLanguage');
        $entityDetail->personalCodeAttribute = $attributes->findByUrn('urn:schac:dir:attribute-def:schacPersonalUniqueCode');
        $entityDetail->scopedAffiliationAttribute = $attributes->findByUrn('urn:mace:dir:attribute-def:eduPersonScopedAffiliation');
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
        if ($this->getProtocol() !== Constants::TYPE_OPENID_CONNECT) {
            return $this->entityId;
        }
        return OidcClientIdParser::parse($this->entityId);
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

    /**
     * @return EntityActions
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * @return string
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * @return string[]
     */
    public function getRedirectUris()
    {
        return $this->redirectUris;
    }

    /**
     * @return string
     */
    public function getGrantType()
    {
        return $this->grantType;
    }

    /**
     * @return bool
     */
    public function isPlaygroundEnabled()
    {
        return $this->playgroundEnabled;
    }

    /**
     * @return int
     */
    public function getAccessTokenValidity()
    {
        return $this->accessTokenValidity;
    }

    /**
     * @return bool
     */
    public function isPublicClient()
    {
        return $this->isPublicClient;
    }

    /**
     * @return ManageEntity[]|null
     */
    public function getResourceServers()
    {
        return $this->resourceServers;
    }
}
