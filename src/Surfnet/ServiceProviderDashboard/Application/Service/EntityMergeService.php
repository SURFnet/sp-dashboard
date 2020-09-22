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

namespace Surfnet\ServiceProviderDashboard\Application\Service;

use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveEntityCommandInterface;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveOidcngEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveOidcngResourceServerEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveSamlEntityCommand;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\AllowedIdentityProviders;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Attribute;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\AttributeList;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Coin;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Contact;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\ContactList;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Logo;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\OidcngClient;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\OidcngResourceServerClient;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Organization;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Protocol;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\MetaData;
use Surfnet\ServiceProviderDashboard\Domain\Repository\AttributesMetadataRepository;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Secret;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EntityMergeService
{
    /**
     * @var AttributesMetadataRepository
     */
    private $attributeRepository;

    public function __construct(AttributesMetadataRepository $repository)
    {
        $this->attributeRepository = $repository;
    }

    public function mergeEntityCommand(SaveEntityCommandInterface $command, ?ManageEntity $manageEntity = null): ManageEntity
    {
        $metaData = new MetaData(
            $command->getEntityId(),
            $command->getMetadataUrl(),
            $command->getAcsLocation(),
            $command->getNameIdFormat(),
            $command->getCertificate(),
            $command->getDescriptionEn(),
            $command->getDescriptionNl(),
            $command->getNameEn(),
            $command->getNameNl(),
            $this->buildContactListFromCommand($command),
            $this->buildOrganizationFromCommand($command),
            $this->buildCoinFromCommand($command),
            $this->buildLogoFromCommand($command)
        );
        $attributes = $this->buildAttributesFromCommand($command);
        $protocol = new Protocol($command->getProtocol());

        $allowedIdPs = null;
        if ($manageEntity && $manageEntity->getAllowedIdentityProviders()) {
            $allowedIdPs = new AllowedIdentityProviders(
                $manageEntity->getAllowedIdentityProviders()->getAllowedIdentityProviders(),
                $manageEntity->getAllowedIdentityProviders()->isAllowAll()
            );
        }

        $oidcClient = null;
        $secret = null;
        if (!$manageEntity && $protocol->getProtocol() !== Constants::TYPE_SAML) {
            $secret = new Secret(20);
        }
        if ($protocol->getProtocol() === Constants::TYPE_OPENID_CONNECT_TNG) {
            /** @var SaveOidcngEntityCommand  $command */
            $oidcClient = new OidcngClient(
                $command->getClientId(),
                $secret->getSecret(),
                $command->getRedirectUrls(),
                $command->getGrantType(),
                $command->getScopes(),
                $command->isPublicClient(),
                $command->getAccessTokenValidity(),
                $command->getOidcngResourceServers()
            );
        } else if ($protocol->getProtocol() === Constants::TYPE_OPENID_CONNECT_TNG_RESOURCE_SERVER) {
            /** @var SaveOidcngResourceServerEntityCommand  $command */
            $oidcClient = new OidcngResourceServerClient(
                $command->getClientId(),
                $secret->getSecret(),
                null,
                $command->getScopes()
            );
        }

        $newEntity = new ManageEntity(
            null,
            $attributes,
            $metaData,
            $allowedIdPs,
            $protocol,
            $oidcClient,
            $command->getService()
        );
        $newEntity->setComments($command->getComments());
        $newEntity->setEnvironment($command->getEnvironment());
        // If no existing ManageEntity is provided, then return the newly created entity
        if (!$manageEntity) {
            return $newEntity;
        }
        $manageEntity->merge($newEntity);
        return $manageEntity;
    }

    /**
     * Using the attribute repository, map the attributes set on the command to a list of attributes that can
     * be set on the ManageEntity
     */
    private function buildAttributesFromCommand(SaveEntityCommandInterface $command)
    {
        $attributeList = new AttributeList();

        // Oidc TNG resource servers do not track attributes in manage
        if ($command instanceof SaveOidcngResourceServerEntityCommand) {
            return $attributeList;
        }

        foreach ($this->attributeRepository->findAll() as $definition) {
            $getterName = $definition->getterName;

            if ($command->$getterName()) {
                $commandAttribute = $command->$getterName();
                $urn = reset($definition->urns);
                $attributeList->add(new Attribute($urn, '', 'idp', $commandAttribute->getMotivation()));
                $attributeList->add(new Attribute($urn, '', 'idp', $commandAttribute->getMotivation()));
            }
        }
        return $attributeList;
    }

    private function buildContactListFromCommand(SaveEntityCommandInterface $command): ContactList
    {
        $contactList = new ContactList();
        if ($command->getTechnicalContact()) {
            $contactList->add(Contact::fromContact($command->getTechnicalContact(), 'technical'));
        }
        if ($command->getAdministrativeContact()) {
            $contactList->add(Contact::fromContact($command->getAdministrativeContact(), 'administrative'));
        }
        if ($command->getSupportContact()) {
            $contactList->add(Contact::fromContact($command->getSupportContact(), 'support'));
        }
        return $contactList;
    }

    private function buildOrganizationFromCommand(SaveEntityCommandInterface $command): Organization
    {
        return new Organization(
            $command->getOrganizationNameEn(),
            $command->getOrganizationDisplayNameEn(),
            $command->getOrganizationUrlEn(),
            $command->getOrganizationNameNl(),
            $command->getOrganizationDisplayNameNl(),
            $command->getOrganizationUrlNl()
        );
    }

    private function buildCoinFromCommand(SaveEntityCommandInterface $command): Coin
    {
        return new Coin(
            null,
            null,
            null,
            null,
            $command->getApplicationUrl(),
            $command->getEulaUrl(),
            null
        );
    }

    private function buildLogoFromCommand(SaveEntityCommandInterface $command): Logo
    {
        return new Logo($command->getLogoUrl(), null, null);
    }
}
