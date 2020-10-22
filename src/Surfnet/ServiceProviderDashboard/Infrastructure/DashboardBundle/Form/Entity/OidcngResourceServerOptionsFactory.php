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

use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\Repository\QueryEntityRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Exception\InvalidEnvironmentException;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\QueryClient;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Exception\QueryServiceProviderException as QueryServiceProviderExceptionAlias;

class OidcngResourceServerOptionsFactory
{
    private $testEntityRepository;

    private $productionRepository;

    private $testPublicationState;

    private $productionPublicationState;

    public function __construct(
        QueryEntityRepository $testEntityRepository,
        QueryEntityRepository $productionRepository,
        string $testPublicationState,
        string $productionPublicationState
    ) {
        $this->testEntityRepository = $testEntityRepository;
        $this->productionRepository = $productionRepository;
        $this->testPublicationState = $testPublicationState;
        $this->productionPublicationState = $productionPublicationState;
    }


    /**
     * @throws InvalidEnvironmentException
     * @throws QueryServiceProviderExceptionAlias
     */
    public function build(string $teamName, string $environment): array
    {
        switch ($environment) {
            case Constants::ENVIRONMENT_TEST:
                return $this->createChoicesFrom(
                    $this->testEntityRepository->findOidcngResourceServersByTeamName($teamName, $this->testPublicationState)
                );

            case Constants::ENVIRONMENT_PRODUCTION:
                return  $this->createChoicesFrom(
                    $this->productionRepository->findOidcngResourceServersByTeamName(
                        $teamName,
                        $this->productionPublicationState
                    )
                );

            default:
                throw new InvalidEnvironmentException(sprintf('Environment "%s" is not supported', $environment));
        }
    }

    /**
     * @param ManageEntity[] $entities
     */
    private function createChoicesFrom(array $entities): array
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
