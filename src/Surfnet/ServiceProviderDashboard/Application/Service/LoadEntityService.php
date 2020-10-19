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
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Coin;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Repository\EntityRepository;
use Surfnet\ServiceProviderDashboard\Domain\Repository\QueryEntityRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Exception\QueryServiceProviderException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LoadEntityService
{
    /**
     * @var EntityRepository
     */
    private $entityRepository;

    /**
     * @var QueryEntityRepository
     */
    private $manageTestClient;

    /**
     * @var QueryEntityRepository
     */
    private $manageProductionClient;

    public function __construct(
        EntityRepository $entityRepository,
        QueryEntityRepository $manageTestClient,
        QueryEntityRepository $manageProductionClient
    ) {
        $this->entityRepository = $entityRepository;
        $this->manageTestClient = $manageTestClient;
        $this->manageProductionClient = $manageProductionClient;
    }

    /**
     * @param int $dashboardId
     * @param string $manageId
     * @param Service $service
     * @param string $sourceEnvironment
     * @param string $environment
     * @param bool $isClientReset
     * @return ManageEntity
     * @throws InvalidArgumentException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function load($dashboardId, $manageId, Service $service, $sourceEnvironment, $environment, $isClientReset = false)
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

        $manageEntity->setEnvironment($sourceEnvironment);
        $manageEntity->setService($service);

        // Published production entities must be cloned, not copied
        $isProductionClone = $environment === Constants::ENVIRONMENT_PRODUCTION && $manageStagingState === 0;
        // Entities copied from test to prod should not have a manage id either
        $isCopyToProduction = $environment === Constants::ENVIRONMENT_PRODUCTION && $sourceEnvironment === Constants::ENVIRONMENT_TEST  ;
        if (($isProductionClone || $isCopyToProduction) && !$isClientReset) {
            $manageEntity = $manageEntity->resetId();
            $manageEntity->setEnvironment($environment);
        }

        $protocol = $manageEntity->getProtocol()->getProtocol();
        if ($isCopyToProduction &&
            ($protocol === Constants::TYPE_OPENID_CONNECT_TNG ||
            $protocol === Constants::TYPE_OPENID_CONNECT_TNG_RESOURCE_SERVER)
        ) {
            $manageEntity->getOidcClient()->resetResourceServers();
            $manageEntity->getMetaData()->resetOidcNgEntitId();
        }

        // Return the entity
        return $manageEntity;
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
}
