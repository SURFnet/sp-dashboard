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
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveOauthClientCredentialClientCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveOidcngEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveOidcngResourceServerEntityCommand;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\AllowedIdentityProviders;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Attribute;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\AttributeList;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Coin;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Contact;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\ContactList;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Logo;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\OauthClientCredentialsClientClient;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\OidcngClient;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\OidcngResourceServerClient;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Organization;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Protocol;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\MetaData;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\NullSecret;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Secret;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\TypeOfServiceCollection;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EntityMergeService
{
    public function __construct(
        private readonly AttributeServiceInterface $attributeService,
        private readonly string $playGroundUriTest,
        private readonly string $playGroundUriProd,
    ) {
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function mergeEntityCommand(
        SaveEntityCommandInterface $command,
        ?ManageEntity $manageEntity = null,
    ): ManageEntity {
        $metaData = new MetaData(
            $command->getEntityId(),
            $command->getMetadataUrl(),
            $command->getAcsLocations(),
            $command->getNameIdFormat(),
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

        // Create an empty AllowedIdentityProviders with the default values, if no specific values are provided,
        // use this config as default.
        $allowedIdPs = new AllowedIdentityProviders([], true);
        if ($manageEntity && $manageEntity->getAllowedIdentityProviders()) {
            $allowedIdPs = new AllowedIdentityProviders(
                $manageEntity->getAllowedIdentityProviders()->getAllowedIdentityProviders(),
                $manageEntity->getAllowedIdentityProviders()->isAllowAll()
            );
        }

        $oidcClient = null;
        $isNewEntity = !$manageEntity;
        $isCopyToProduction = $manageEntity && $manageEntity->getId() === null;

        $secret = new NullSecret();
        // Set the client secret when createing a new entity or when we are copying an entity.
        if ($protocol->getProtocol() !== Constants::TYPE_SAML && ($isNewEntity || $isCopyToProduction)) {
            $secret = new Secret(20);
        }
        if ($protocol->getProtocol() === Constants::TYPE_OPENID_CONNECT_TNG) {
            /**
 * @var SaveOidcngEntityCommand  $command
*/
            $oidcClient = new OidcngClient(
                $command->getClientId(),
                $secret->getSecret(),
                $this->buildRedirectUrls($command),
                $command->getGrants(),
                $command->isPublicClient(),
                $command->getAccessTokenValidity(),
                $command->getOidcngResourceServers()
            );
        } elseif ($protocol->getProtocol() === Constants::TYPE_OPENID_CONNECT_TNG_RESOURCE_SERVER) {
            /**
 * @var SaveOidcngResourceServerEntityCommand  $command
*/
            $oidcClient = new OidcngResourceServerClient(
                $command->getClientId(),
                $secret->getSecret(),
                []
            );
        } elseif ($protocol->getProtocol() === Constants::TYPE_OAUTH_CLIENT_CREDENTIAL_CLIENT) {
            /**
 * @var SaveOauthClientCredentialClientCommand $command
*/
            $oidcClient = new OauthClientCredentialsClientClient(
                $command->getClientId(),
                $command->getAccessTokenValidity(),
                $secret->getSecret(),
                $command->getOidcngResourceServers()
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
        if (!$manageEntity instanceof ManageEntity) {
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

        if ($this->isNotTrackedInManage($command)) {
            return $attributeList;
        }

        foreach ($this->attributeService->getEntityMergeAttributes() as $entityMergeAttribute) {
            if ($command->getAttribute($entityMergeAttribute->getName())) {
                $commandAttribute = $command->getAttribute($entityMergeAttribute->getName());
                $attributeList->add(
                    new Attribute(
                        $entityMergeAttribute->getUrn(),
                        '',
                        'idp',
                        $commandAttribute->getMotivation(),
                        '',
                        false
                    )
                );
            }
        }
        return $attributeList;
    }

    private function buildContactListFromCommand(SaveEntityCommandInterface $command): ContactList
    {
        $contactList = new ContactList();
        if ($command->getTechnicalContact() && $command->getTechnicalContact()->isContactSet()) {
            $contactList->add(Contact::fromContact($command->getTechnicalContact(), 'technical'));
        }
        if ($command->getAdministrativeContact() && $command->getAdministrativeContact()->isContactSet()) {
            $contactList->add(Contact::fromContact($command->getAdministrativeContact(), 'administrative'));
        }
        if ($command->getSupportContact() && $command->getSupportContact()->isContactSet()) {
            $contactList->add(Contact::fromContact($command->getSupportContact(), 'support'));
        }
        return $contactList;
    }

    private function buildOrganizationFromCommand(SaveEntityCommandInterface $command): Organization
    {
        return new Organization(
            $command->getService()->getOrganizationNameEn(),
            null,
            null,
            $command->getService()->getOrganizationNameNl(),
            null,
            null
        );
    }

    private function buildCoinFromCommand(SaveEntityCommandInterface $command): Coin
    {
        $typeOfServiceCollection = new TypeOfServiceCollection();
        foreach ($command->getTypeOfService() as $typeOfService) {
            $typeOfServiceCollection->add($typeOfService);
        }

        return new Coin(
            null,
            null,
            null,
            null,
            $command->getApplicationUrl(),
            $typeOfServiceCollection,
            $command->getEulaUrl(),
            null,
            null,
            // Note when the dashboard sets the isPublicInDashboard to be true
            // That means the metaDataFields.coin:ss:idp_visible_only must be false
            !$command->isPublicInDashboard(),
        );
    }

    private function buildLogoFromCommand(SaveEntityCommandInterface $command): Logo
    {
        return new Logo($command->getLogoUrl(), null, null);
    }

    private function buildRedirectUrls(SaveOidcngEntityCommand $command): ?array
    {
        $playgroundUrl = $command->getEnvironment() === Constants::ENVIRONMENT_TEST ? $this->playGroundUriTest : $this->playGroundUriProd;
        $urls = $command->getRedirectUrls();
        // Add the playground URL if requested in the form (checkbox was checked)
        if ($command->isEnablePlayground() && !in_array($playgroundUrl, $urls)) {
            $urls[] = $playgroundUrl;
        }
        // Test if we should remove the playground URL (checkbox unchecked, but url remained in set)
        if (!$command->isEnablePlayground() && in_array($playgroundUrl, $urls)) {
            foreach ($urls as $key => $url) {
                if ($url === $playgroundUrl) {
                    unset($urls[$key]);
                }
            }
        }
        return $urls;
    }

    /**
     * @param SaveEntityCommandInterface $command
     * @return bool
     */
    private function isNotTrackedInManage(SaveEntityCommandInterface $command): bool
    {
        // Oidc TNG resource servers do not track attributes in manage
        // Neither do the Oauth Client Credentials clients

        return
            $command instanceof SaveOidcngResourceServerEntityCommand ||
            $command instanceof SaveOauthClientCredentialClientCommand;
    }
}
