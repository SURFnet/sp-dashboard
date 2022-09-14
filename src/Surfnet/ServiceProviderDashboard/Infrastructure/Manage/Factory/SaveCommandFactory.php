<?php

/**
 * Copyright 2020 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Factory;

use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveEntityCommandInterface;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveOauthClientCredentialClientCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveOidcngEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveOidcngResourceServerEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveSamlEntityCommand;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\AttributeList;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\OidcClientInterface;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Contact;
use Surfnet\ServiceProviderDashboard\Legacy\Repository\AttributesMetadataRepository;
use function is_array;
use function reset;

class SaveCommandFactory implements SaveCommandFactoryInterface
{
    /**
     * @var AttributesMetadataRepository
     */
    private $attributeRepository;

    /**
     * @var string
     */
    private $playGroundUriProd;

    /**
     * @var string
     */
    private $playGroundUriTest;

    public function __construct(
        AttributesMetadataRepository $attributeRepository,
        string $oidcPlaygroundUriTest,
        string $oidcPlaygroundUriProd
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->playGroundUriTest = $oidcPlaygroundUriTest;
        $this->playGroundUriProd = $oidcPlaygroundUriProd;
    }

    public function buildSamlCommandByManageEntity(
        ManageEntity $manageEntity,
        string $environment
    ): SaveSamlEntityCommand {
        $command = new SaveSamlEntityCommand();
        $metaData = $manageEntity->getMetaData();
        $coins = $manageEntity->getMetaData()->getCoin();

        $command->setId($manageEntity->getId());
        $command->setManageId($manageEntity->getId());
        $command->setStatus($manageEntity->getStatus());
        $command->setEnvironment($environment);
        $command->setMetadataUrl($metaData->getMetaDataUrl());
        $command->setAcsLocations($metaData->getAcsLocations());
        $command->setEntityId($metaData->getEntityId());
        $command->setCertificate($metaData->getCertData());
        $command->setLogoUrl($metaData->getLogo()->getUrl());
        $command->setAdministrativeContact(Contact::from($metaData->getContacts()->findAdministrativeContact()));
        $command->setTechnicalContact(Contact::from($metaData->getContacts()->findTechnicalContact()));
        $command->setSupportContact(Contact::from($metaData->getContacts()->findSupportContact()));
        $command->setNameIdFormat($metaData->getNameIdFormat());

        // Organization data
        $command->setNameNl($metaData->getNameNl());
        $command->setNameEn($metaData->getNameEn());
        $command->setDescriptionNl($metaData->getDescriptionNl());
        $command->setDescriptionEn($metaData->getDescriptionEn());

        // Coin data
        $command->setApplicationUrl($coins->getApplicationUrl());
        $command->setEulaUrl($coins->getEula());
        $command->setImportUrl($coins->getOriginalMetadataUrl());

        // Attributes
        $this->setAttributes($command, $manageEntity->getAttributes());

        return $command;
    }

    public function buildOidcngCommandByManageEntity(ManageEntity $manageEntity, string $environment, bool $isCopy = false): SaveOidcngEntityCommand
    {
        $command = new SaveOidcngEntityCommand();
        $metaData = $manageEntity->getMetaData();
        $coins = $manageEntity->getMetaData()->getCoin();

        $command->setId($manageEntity->getId());
        $command->setManageId($manageEntity->getId());
        $command->setStatus($manageEntity->getStatus());
        $command->setEnvironment($environment);
        $command->setEntityId($metaData->getEntityId());
        $command->setLogoUrl($metaData->getLogo()->getUrl());
        $command->setAdministrativeContact(Contact::from($metaData->getContacts()->findAdministrativeContact()));
        $command->setTechnicalContact(Contact::from($metaData->getContacts()->findTechnicalContact()));
        $command->setSupportContact(Contact::from($metaData->getContacts()->findSupportContact()));
        $command->setIsCopy($isCopy);

        // Organization data
        $command->setNameNl($metaData->getNameNl());
        $command->setNameEn($metaData->getNameEn());
        $command->setDescriptionNl($metaData->getDescriptionNl());
        $command->setDescriptionEn($metaData->getDescriptionEn());

        // Coin data
        $command->setApplicationUrl($coins->getApplicationUrl());
        $command->setEulaUrl($coins->getEula());

        // Attributes
        $this->setAttributes($command, $manageEntity->getAttributes());

        // OidcNg settings
        $command->setSecret($manageEntity->getOidcClient()->getClientSecret());
        $command->setGrants($manageEntity->getOidcClient()->getGrants());

        // The SAML nameidformat is used as the OIDC subject type https://www.pivotaltracker.com/story/show/167511146
        $command->setSubjectType($metaData->getNameIdFormat());
        $command->setIsPublicClient($manageEntity->getOidcClient()->isPublicClient());
        $command->setAccessTokenValidity($manageEntity->getOidcClient()->getAccessTokenValidity());
        $command->setOidcngResourceServers($manageEntity->getOidcClient()->getResourceServers());

        $resourceServers = $command->getOidcngResourceServers();
        if (is_array($resourceServers) && reset($resourceServers) instanceof ManageEntity) {
            $resourceServers = $command->getOidcngResourceServers();
            $servers = [];
            foreach ($resourceServers as $resourceServer) {
                $servers[$resourceServer->getMetaData()->getEntityId()] = $resourceServer->getMetaData()->getEntityId();
            }
            $command->setOidcngResourceServers($servers);
        }

        $this->setRedirectUris($command, $manageEntity, $environment);

        return $command;
    }

    public function buildOidcngRsCommandByManageEntity(
        ManageEntity $manageEntity,
        string $environment,
        bool $isCopy = false
    ): SaveOidcngResourceServerEntityCommand {
        $command = new SaveOidcngResourceServerEntityCommand();
        $metaData = $manageEntity->getMetaData();

        $command->setId($manageEntity->getId());
        $command->setManageId($manageEntity->getId());
        $command->setStatus($manageEntity->getStatus());
        $command->setEnvironment($environment);
        $command->setEntityId($metaData->getEntityId());
        $command->setIsCopy($isCopy);

        $command->setSecret($manageEntity->getOidcClient()->getClientSecret());

        $command->setNameNl($metaData->getNameNl());
        $command->setNameEn($metaData->getNameEn());
        $command->setDescriptionNl($metaData->getDescriptionNl());
        $command->setDescriptionEn($metaData->getDescriptionEn());

        $command->setAdministrativeContact(Contact::from($metaData->getContacts()->findAdministrativeContact()));
        $command->setTechnicalContact(Contact::from($metaData->getContacts()->findTechnicalContact()));
        $command->setSupportContact(Contact::from($metaData->getContacts()->findSupportContact()));

        return $command;
    }

    public function buildOauthCccCommandByManageEntity(
        ManageEntity $manageEntity,
        string $environment,
        bool $isCopy = false
    ): SaveOauthClientCredentialClientCommand {
        $command = new SaveOauthClientCredentialClientCommand();
        $metaData = $manageEntity->getMetaData();
        $coins = $manageEntity->getMetaData()->getCoin();

        $command->setId($manageEntity->getId());
        $command->setManageId($manageEntity->getId());
        $command->setStatus($manageEntity->getStatus());
        $command->setEnvironment($environment);
        $command->setEntityId($metaData->getEntityId());
        $command->setIsCopy($isCopy);

        $command->setSecret($manageEntity->getOidcClient()->getClientSecret());
        $command->setLogoUrl($metaData->getLogo()->getUrl());

        $command->setNameNl($metaData->getNameNl());
        $command->setNameEn($metaData->getNameEn());
        $command->setDescriptionNl($metaData->getDescriptionNl());
        $command->setDescriptionEn($metaData->getDescriptionEn());

        $command->setAccessTokenValidity($manageEntity->getOidcClient()->getAccessTokenValidity());
        $resourceServers = $command->getOidcngResourceServers();
        if (is_array($resourceServers) && reset($resourceServers) instanceof ManageEntity) {
            $resourceServers = $command->getOidcngResourceServers();
            $servers = [];
            foreach ($resourceServers as $resourceServer) {
                $servers[$resourceServer->getMetaData()->getEntityId()] = $resourceServer->getMetaData()->getEntityId();
            }
            $command->setOidcngResourceServers($servers);
        }

        // Coin data
        $command->setApplicationUrl($coins->getApplicationUrl());
        $command->setEulaUrl($coins->getEula());

        $command->setAdministrativeContact(Contact::from($metaData->getContacts()->findAdministrativeContact()));
        $command->setTechnicalContact(Contact::from($metaData->getContacts()->findTechnicalContact()));
        $command->setSupportContact(Contact::from($metaData->getContacts()->findSupportContact()));

        return $command;
    }

    private function setAttributes(SaveEntityCommandInterface $command, AttributeList $attributeList)
    {
        foreach ($this->attributeRepository->findAll() as $attributeDefinition) {
            $urn = reset($attributeDefinition->urns);
            $manageAttribute = $attributeList->findByUrn($urn);
            if (!$manageAttribute) {
                continue;
            }

            $attribute = new Attribute();
            $attribute->setRequested(true);
            $attribute->setMotivation($manageAttribute->getMotivation());

            $setter = $attributeDefinition->setterName;
            $command->{$setter}($attribute);
        }
    }

    private function setRedirectUris(SaveEntityCommandInterface $command, ManageEntity $manageEntity, string $environment)
    {
        $redirectUris = $manageEntity->getOidcClient()->getRedirectUris();
        $playGroundUri = ($environment === Constants::ENVIRONMENT_PRODUCTION ? $this->playGroundUriProd : $this->playGroundUriTest);
        if (in_array($playGroundUri, $redirectUris)) {
            $key = array_search($playGroundUri, $redirectUris);
            if ($key !== false) {
                unset($redirectUris[$key]);
                $command->setEnablePlayground(true);
            }
        }
        $command->setRedirectUrls($redirectUris);
    }
}
