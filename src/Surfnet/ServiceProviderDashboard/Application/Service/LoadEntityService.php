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

namespace Surfnet\ServiceProviderDashboard\Application\Service;

use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Application\Parser\OidcngClientIdParser;
use Surfnet\ServiceProviderDashboard\Application\Parser\OidcngSpdClientIdParser;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Repository\AttributesMetadataRepository;
use Surfnet\ServiceProviderDashboard\Domain\Repository\EntityRepository;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\ResourceServerCollection;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\QueryClient as ManageClient;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Dto\Coin;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Dto\Protocol;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Exception\QueryServiceProviderException;
use Webmozart\Assert\Assert;

class LoadEntityService
{
    /**
     * @var EntityRepository
     */
    private $entityRepository;

    /**
     * @var ManageClient
     */
    private $manageTestClient;

    /**
     * @var ManageClient
     */
    private $manageProductionClient;

    /**
     * @var AttributesMetadataRepository
     */
    private $attributeMetadataRepository;
    /**
     * @var string
     */
    private $oidcPlaygroundUriTest;
    /**
     * @var string
     */
    private $oidcPlaygroundUriProd;
    /**
     * @var string
     */
    private $oidcngPlaygroundUriTest;
    /**
     * @var string
     */
    private $oidcngPlaygroundUriProd;

    /**
     * @param EntityRepository $entityRepository
     * @param ManageClient $manageTestClient
     * @param ManageClient $manageProductionClient
     * @param AttributesMetadataRepository $attributeMetadataRepository
     * @param string $oidcPlaygroundUriTest
     * @param string $oidcPlaygroundUriProd
     * @param string $oidcngPlaygroundUriTest
     * @param string $oidcngPlaygroundUriProd
     */
    public function __construct(
        EntityRepository $entityRepository,
        ManageClient $manageTestClient,
        ManageClient $manageProductionClient,
        AttributesMetadataRepository $attributeMetadataRepository,
        $oidcPlaygroundUriTest,
        $oidcPlaygroundUriProd,
        $oidcngPlaygroundUriTest,
        $oidcngPlaygroundUriProd
    ) {
        Assert::stringNotEmpty($oidcPlaygroundUriTest, 'Please set "playground_uri_test" in parameters.yml');
        Assert::stringNotEmpty($oidcPlaygroundUriProd, 'Please set "playground_uri_prod" in parameters.yml');
        Assert::stringNotEmpty($oidcngPlaygroundUriTest, 'Please set "oidcng_playground_uri_test" in parameters.yml');
        Assert::stringNotEmpty($oidcngPlaygroundUriProd, 'Please set "oidcng_playground_uri_prod" in parameters.yml');

        $this->entityRepository = $entityRepository;
        $this->manageTestClient = $manageTestClient;
        $this->manageProductionClient = $manageProductionClient;
        $this->attributeMetadataRepository = $attributeMetadataRepository;
        $this->oidcPlaygroundUriTest = $oidcPlaygroundUriTest;
        $this->oidcPlaygroundUriProd = $oidcPlaygroundUriProd;
        $this->oidcngPlaygroundUriTest = $oidcngPlaygroundUriTest;
        $this->oidcngPlaygroundUriProd = $oidcngPlaygroundUriProd;
    }

    /**
     * @param int $dashboardId
     * @param string $manageId
     * @param Service $service
     * @param string $sourceEnvironment
     * @param string $environment
     * @return Entity
     * @throws InvalidArgumentException
     * @throws QueryServiceProviderException
     */
    public function load($dashboardId, $manageId, Service $service, $sourceEnvironment, $environment)
    {
        if (!$this->entityRepository->isUnique($dashboardId)) {
            throw new InvalidArgumentException(
                'The id that was generated for the entity was not unique, please try again'
            );
        }

        $manageClient = $this->manageProductionClient;
        if ($sourceEnvironment == 'test') {
            $manageClient = $this->manageTestClient;
        }

        $manageEntity = $manageClient->findByManageId($manageId);

        if (empty($manageEntity)) {
            throw new InvalidArgumentException(
                'Could not find entity in manage: '.$manageId
            );
        }

        $manageTeamName = $manageEntity->getMetaData()->getCoin()->getServiceTeamId();
        $manageStagingState = $this->getManageStagingState($manageEntity->getMetaData()->getCoin());

        if ($manageTeamName !== $service->getTeamName()) {
            throw new InvalidArgumentException(
                sprintf(
                    'The entity you are about to copy does not belong to the selected team: %s != %s',
                    $manageTeamName,
                    $service->getTeamName()
                )
            );
        }

        // Convert manage entity to domain entity
        $domainEntity = Entity::fromManageResponse(
            $manageEntity,
            $sourceEnvironment,
            $service,
            $this->oidcPlaygroundUriTest,
            $this->oidcPlaygroundUriProd,
            $this->oidcngPlaygroundUriTest,
            $this->oidcngPlaygroundUriProd
        );

        // Set some defaults
        $domainEntity->setStatus(Entity::STATE_PUBLISHED);
        $domainEntity->setId($dashboardId);
        $domainEntity->setManageId($manageId);

        // Published production entities must be cloned, not copied
        $isProductionClone = $environment == 'production' && $manageStagingState === 0;
        // Entities copied from test to prod should not have a manage id either
        $isCopyToProduction = $environment == 'production' && $sourceEnvironment == 'test';
        if ($isProductionClone || $isCopyToProduction) {
            $domainEntity->setManageId(null);
        }

        $this->updateAllowedResourceServers($isCopyToProduction, $domainEntity);
        $this->updateClientId($isCopyToProduction, $domainEntity);

        // Set the target environment
        $domainEntity->setEnvironment($environment);

        // Return the entity
        return $domainEntity;
    }

    /**
     * Determine the staging state
     *
     * The state is based on the presence of the coin:exclude_from_push attribute.
     *
     * 0 means this is a production entity.
     * 1 means the entity is still in staging (access was requested).
     *
     * @param Coin $coin
     * @return int
     */
    private function getManageStagingState(Coin $coin)
    {
        if ($coin->getExcludeFromPush() === 1) {
            return 1;
        }
        return 0;
    }

    /**
     * When copying a OIDC TNG entity to production, we do NOT copy the related resource server references
     * along with it. As they do not exist on the other environment.
     *
     * @param bool $isCopyToProduction
     * @param Entity $domainEntity
     */
    private function updateAllowedResourceServers($isCopyToProduction, Entity $domainEntity)
    {
        if ($isCopyToProduction && $domainEntity->getProtocol() === Entity::TYPE_OPENID_CONNECT_TNG) {
            $domainEntity->setOidcngResourceServers(new ResourceServerCollection([]));
        }
    }

    /**
     * Only when an OIDC TNG entity is copied to production, do we append the https schema back onto the schema-less
     * client id. This to prevent a validation error when publishing the draft entity (what a copied to production
     * entity basically is).
     *
     * @param bool $isCopyToProduction
     * @param Entity $domainEntity
     */
    private function updateClientId($isCopyToProduction, Entity $domainEntity)
    {
        $protocol = $domainEntity->getProtocol();
        $isOidcngEntity = $protocol === Entity::TYPE_OPENID_CONNECT_TNG
            || $protocol === Entity::TYPE_OPENID_CONNECT_TNG_RESOURCE_SERVER;
        if ($isCopyToProduction && $isOidcngEntity) {
            $clientId = OidcngSpdClientIdParser::parse($domainEntity->getEntityId());
            $domainEntity->setEntityId($clientId);
        }
    }
}
