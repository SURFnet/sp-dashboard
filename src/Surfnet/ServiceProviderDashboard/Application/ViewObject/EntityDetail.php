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

use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\AttributeList;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Contact;
use function in_array;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.UnusedPrivateField)
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
    private $eduPersonTargetedIDAttribute;

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
     * @var Attribute
     */
    private $organizationUnitAttribute;

    /**
     * @var EntityActions
     */
    private $actions;

    /**
     * @var string[]
     */
    private $redirectUris;

    /**
     * @var array
     */
    private $grants;

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

    public static function fromEntity(
        ManageEntity $entity,
        string $oidcPlaygroundUriTest,
        string $oidcPlaygroundUriProd
    ) : EntityDetail {
        $entityDetail = new self();
        $entityDetail->id = $entity->getId();
        $entityDetail->manageId = $entity->getId();
        $entityDetail->protocol = $entity->getProtocol()->getProtocol();

        if ($entity->getProtocol()->getProtocol() === Constants::TYPE_OPENID_CONNECT_TNG ||
            $entity->getProtocol()->getProtocol() === Constants::TYPE_OAUTH_CLIENT_CREDENTIAL_CLIENT
        ) {
            $entityDetail->grants = $entity->getOidcClient()->getGrants();
            $entityDetail->isPublicClient = $entity->getOidcClient()->isPublicClient();
            $entityDetail->accessTokenValidity = $entity->getOidcClient()->getAccessTokenValidity();
            $entityDetail->redirectUris = $entity->getOidcClient()->getRedirectUris();
            $entityDetail->playgroundEnabled = self::getIsPlaygroundEnabled(
                $entity,
                $oidcPlaygroundUriTest,
                $oidcPlaygroundUriProd
            );
            $entityDetail->resourceServers = $entity->getOidcClient()->getResourceServers();
        }

        $entityDetail->metadataUrl = $entity->getMetaData()->getMetaDataUrl();
        $entityDetail->acsLocation = $entity->getMetaData()->getAcsLocation();
        $entityDetail->entityId = $entity->getMetaData()->getEntityId();
        $entityDetail->certificate = $entity->getMetaData()->getCertData();
        if ($entity->getMetaData()->getLogo()) {
            $entityDetail->logoUrl = $entity->getMetaData()->getLogo()->getUrl();
        }
        $entityDetail->nameNl = $entity->getMetaData()->getNameNl();
        $entityDetail->nameEn = $entity->getMetaData()->getNameEn();
        $entityDetail->descriptionNl = $entity->getMetaData()->getDescriptionNl();
        $entityDetail->descriptionEn = $entity->getMetaData()->getDescriptionEn();
        $entityDetail->applicationUrl = $entity->getMetaData()->getCoin()->getApplicationUrl();
        $entityDetail->eulaUrl = $entity->getMetaData()->getCoin()->getEula();
        $entityDetail->administrativeContact = $entity->getMetaData()->getContacts()->findAdministrativeContact();
        $entityDetail->technicalContact = $entity->getMetaData()->getContacts()->findTechnicalContact();
        $entityDetail->supportContact = $entity->getMetaData()->getContacts()->findSupportContact();
        $entityDetail->nameIdFormat = $entity->getMetaData()->getNameIdFormat();
        $entityDetail->organizationNameNl = $entity->getMetaData()->getOrganization()->getNameNl();
        $entityDetail->organizationNameEn = $entity->getMetaData()->getOrganization()->getNameEn();
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
            $entity->getProtocol()->getProtocol(),
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
        $entityDetail->surNameAttribute = $attributes->findByUrn('urn:mace:dir:attribute-def:sn');
        $entityDetail->commonNameAttribute = $attributes->findByUrn('urn:mace:dir:attribute-def:cn');
        $entityDetail->displayNameAttribute = $attributes->findByUrn('urn:mace:dir:attribute-def:displayName');
        $entityDetail->emailAddressAttribute = $attributes->findByUrn('urn:mace:dir:attribute-def:mail');
        $entityDetail->organizationAttribute =
            $attributes->findByUrn('urn:mace:terena.org:attribute-def:schacHomeOrganization');
        $entityDetail->organizationTypeAttribute =
            $attributes->findByUrn('urn:mace:terena.org:attribute-def:schacHomeOrganizationType');
        $entityDetail->organizationUnitAttribute = $attributes->findByUrn('urn:mace:dir:attribute-def:ou');
        $entityDetail->affiliationAttribute = $attributes->findByUrn('urn:mace:dir:attribute-def:eduPersonAffiliation');
        $entityDetail->entitlementAttribute = $attributes->findByUrn('urn:mace:dir:attribute-def:eduPersonEntitlement');
        $entityDetail->principleNameAttribute =
            $attributes->findByUrn('urn:mace:dir:attribute-def:eduPersonPrincipalName');
        $entityDetail->eduPersonTargetedIDAttribute =
            $attributes->findByUrn('urn:mace:dir:attribute-def:eduPersonTargetedID');
        $entityDetail->uidAttribute = $attributes->findByUrn('urn:mace:dir:attribute-def:uid');
        $entityDetail->preferredLanguageAttribute =
            $attributes->findByUrn('urn:mace:dir:attribute-def:preferredLanguage');
        $entityDetail->personalCodeAttribute =
            $attributes->findByUrn('urn:schac:attribute-def:schacPersonalUniqueCode');
        $entityDetail->scopedAffiliationAttribute =
            $attributes->findByUrn('urn:mace:dir:attribute-def:eduPersonScopedAffiliation');
    }

    private static function getIsPlaygroundEnabled(
        ManageEntity $entity,
        string $playgroundTest,
        string $playgroundProd
    ): bool {
        $uris = $entity->getOidcClient()->getRedirectUris();
        $environment = $entity->getEnvironment();
        if (($environment === Constants::ENVIRONMENT_TEST && in_array($playgroundTest, $uris)) ||
            ($environment === Constants::ENVIRONMENT_PRODUCTION && in_array($playgroundProd, $uris))
        ) {
            return true;
        }
        return false;
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
     * @return Attribute
     */
    public function getEduPersonTargetedIDAttribute()
    {
        return $this->eduPersonTargetedIDAttribute;
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

    public function getGrants(): array
    {
        return $this->grants;
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

    public function getOrganizationUnitAttribute(): ?Attribute
    {
        return $this->organizationUnitAttribute;
    }
}
