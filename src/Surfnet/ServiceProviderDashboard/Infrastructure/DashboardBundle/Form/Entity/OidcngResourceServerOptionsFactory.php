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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Entity;

use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Exception\InvalidEnvironmentException;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\QueryClient;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Exception\QueryServiceProviderException as QueryServiceProviderExceptionAlias;

class OidcngResourceServerOptionsFactory
{
    /**
     * @var QueryClient
     */
    private $testEntityRepository;

    /**
     * @var QueryClient
     */
    private $productionRepository;

    private $testPublicationState;

    private $productionPublicationState;

    /**
     * @param QueryClient $testEntityRepository
     * @param QueryClient $productionRepository
     * @param string $testPublicationState
     * @param string $productionPublicationState
     */
    public function __construct(
        QueryClient $testEntityRepository,
        QueryClient $productionRepository,
        $testPublicationState,
        $productionPublicationState
    ) {
        $this->testEntityRepository = $testEntityRepository;
        $this->productionRepository = $productionRepository;
        $this->testPublicationState = $testPublicationState;
        $this->productionPublicationState = $productionPublicationState;
    }


    /**
     * @param string $teamName
     * @param string $environment
     * @return array
     * @throws InvalidEnvironmentException
     * @throws QueryServiceProviderExceptionAlias
     */
    public function build($teamName, $environment)
    {
        switch ($environment) {
            case Entity::ENVIRONMENT_TEST:
                return $this->createChoicesFrom(
                    $this->testEntityRepository->findOidcngResourceServersByTeamName($teamName, $this->testPublicationState)
                );
                break;

            case Entity::ENVIRONMENT_PRODUCTION:
                return  $this->createChoicesFrom(
                    $this->productionRepository->findOidcngResourceServersByTeamName(
                        $teamName,
                        $this->productionPublicationState
                    )
                );
                break;

            default:
                throw new InvalidEnvironmentException(sprintf('Environment "%s" is not supported', $environment));
                break;
        }
    }

    /**
     * @param ManageEntity[] $entities
     * @return array
     */
    private function createChoicesFrom(array $entities)
    {
        $choices = [];
        foreach ($entities as $entity) {
            $format = '%s (%s)';
            $clientId = $entity->getOidcClient()->getClientId();
            $choice = sprintf(
                $format,
                $entity->getMetaData()->getNameEn(),
                $clientId
            );
            $choices[$choice] = $clientId;
        }
        return $choices;
    }
}
