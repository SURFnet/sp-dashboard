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
use Surfnet\ServiceProviderDashboard\Domain\Repository\QueryEntityRepository;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LoadEntityService
{
    /**
     * @var QueryEntityRepository
     */
    private $manageTestClient;

    /**
     * @var QueryEntityRepository
     */
    private $manageProductionClient;

    public function __construct(
        QueryEntityRepository $manageTestClient,
        QueryEntityRepository $manageProductionClient
    ) {
        $this->manageTestClient = $manageTestClient;
        $this->manageProductionClient = $manageProductionClient;
    }

    /**
     * @throws InvalidArgumentException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function load(
        string $manageId,
        Service $service,
        string $sourceEnvironment,
        string $environment
    ): ManageEntity {
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
        $manageEntity = $manageEntity->resetId();
        $manageEntity->setEnvironment($environment);

        $isCopyToProduction =
            $environment === Constants::ENVIRONMENT_PRODUCTION && $sourceEnvironment === Constants::ENVIRONMENT_TEST  ;
        $protocol = $manageEntity->getProtocol()->getProtocol();
        $isOidc = $protocol === Constants::TYPE_OPENID_CONNECT_TNG ||
            $protocol === Constants::TYPE_OPENID_CONNECT_TNG_RESOURCE_SERVER;
        if ($isCopyToProduction && $isOidc) {
            $manageEntity->getOidcClient()->resetResourceServers();
            $manageEntity->getMetaData()->resetOidcNgEntitId();
        }

        // Return the entity
        return $manageEntity;
    }
}
