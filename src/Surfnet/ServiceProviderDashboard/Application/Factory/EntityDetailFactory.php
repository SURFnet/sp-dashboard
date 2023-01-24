<?php

declare(strict_types=1);

/**
 * Copyright 2022 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Application\Factory;

use Surfnet\ServiceProviderDashboard\Application\Service\AttributeServiceInterface;
use Surfnet\ServiceProviderDashboard\Application\ViewObject\EntityActions;
use Surfnet\ServiceProviderDashboard\Application\ViewObject\EntityDetail;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;

class EntityDetailFactory
{
    /**
     * @var string
     */
    private $playGroundUriTest;

    /**
     * @var string
     */
    private $playGroundUriProd;

    private $attributeService;

    public function __construct(
        AttributeServiceInterface $attributeService,
        string $playGroundUriTest,
        string $playGroundUriProd
    ) {
        $this->attributeService = $attributeService;
        $this->playGroundUriTest = $playGroundUriTest;
        $this->playGroundUriProd = $playGroundUriProd;
    }

    public function buildFrom(ManageEntity $manageEntity): EntityDetail
    {
        $logo = null;
        if ($manageEntity->getMetaData()->getLogo()) {
            $logo = $manageEntity->getMetaData()->getLogo()->getUrl();
        }

        $actions = new EntityActions(
            $manageEntity->getId(),
            $manageEntity->getService()->getId(),
            $manageEntity->getStatus(),
            $manageEntity->getEnvironment(),
            $manageEntity->getProtocol()->getProtocol(),
            $manageEntity->isReadOnly()
        );

        $grants = null;
        $isPublicClient = false;
        $accessTokenValidity = null;
        $redirectUris = null;
        $playgroundEnabled = null;
        $resourceServers = null;
        if ($manageEntity->getProtocol()->getProtocol() === Constants::TYPE_OPENID_CONNECT_TNG ||
            $manageEntity->getProtocol()->getProtocol() === Constants::TYPE_OAUTH_CLIENT_CREDENTIAL_CLIENT
        ) {
            $grants = $manageEntity->getOidcClient()->getGrants();
            $isPublicClient = $manageEntity->getOidcClient()->isPublicClient();
            $accessTokenValidity = $manageEntity->getOidcClient()->getAccessTokenValidity();
            $redirectUris = $manageEntity->getOidcClient()->getRedirectUris();
            $playgroundEnabled = $this->getIsPlaygroundEnabled($manageEntity);
            $resourceServers = $manageEntity->getOidcClient()->getResourceServers();
        }

        $attributes = $this->attributeService->createEntityDetailAttributes(
            $manageEntity->getAttributes(),
            $manageEntity->getProtocol()->getProtocol()
        );

        return new EntityDetail(
            $manageEntity->getId(),
            $manageEntity->getId(),
            $manageEntity->getMetaData()->getMetaDataUrl(),
            $manageEntity->getMetaData()->getAcsLocations(),
            $manageEntity->getMetaData()->getEntityId(),
            $manageEntity->getProtocol()->getProtocol(),
            $manageEntity->getMetaData()->getCertData(),
            $logo,
            $manageEntity->getMetaData()->getNameNl(),
            $manageEntity->getMetaData()->getNameEn(),
            $manageEntity->getMetaData()->getDescriptionNl(),
            $manageEntity->getMetaData()->getDescriptionEn(),
            $manageEntity->getMetaData()->getCoin()->getApplicationUrl(),
            $manageEntity->getMetaData()->getCoin()->getEula(),
            $manageEntity->getMetaData()->getContacts()->findAdministrativeContact(),
            $manageEntity->getMetaData()->getContacts()->findTechnicalContact(),
            $manageEntity->getMetaData()->getContacts()->findSupportContact(),
            $attributes,
            $actions,
            $manageEntity->getMetaData()->getNameIdFormat(),
            $manageEntity->getMetaData()->getOrganization()->getNameNl(),
            $manageEntity->getMetaData()->getOrganization()->getNameEn(),
            $manageEntity->getMetaData()->getOrganization()->getDisplayNameNl(),
            $manageEntity->getMetaData()->getOrganization()->getDisplayNameEn(),
            $manageEntity->getMetaData()->getOrganization()->getUrlNl(),
            $manageEntity->getMetaData()->getOrganization()->getUrlEn(),
            $redirectUris,
            $grants,
            $playgroundEnabled,
            $accessTokenValidity,
            $isPublicClient,
            $resourceServers
        );
    }

    private function getIsPlaygroundEnabled(ManageEntity $entity): bool
    {
        $uris = $entity->getOidcClient()->getRedirectUris();
        $environment = $entity->getEnvironment();
        return ($environment === Constants::ENVIRONMENT_TEST && in_array($this->playGroundUriTest, $uris)) ||
            ($environment === Constants::ENVIRONMENT_PRODUCTION && in_array($this->playGroundUriProd, $uris));
    }
}
