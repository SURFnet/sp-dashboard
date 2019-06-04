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

namespace Surfnet\ServiceProviderDashboard\Application\CommandHandler\Service;

use Surfnet\ServiceProviderDashboard\Application\CommandHandler\CommandHandler;
use Surfnet\ServiceProviderDashboard\Application\Command\Service\EditServiceCommand;
use Surfnet\ServiceProviderDashboard\Application\Exception\EntityNotFoundException;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Domain\Repository\ServiceRepository;

class EditServiceCommandHandler implements CommandHandler
{
    /**
     * @var ServiceRepository
     */
    private $serviceRepository;

    /**
     * @param ServiceRepository $serviceRepository
     */
    public function __construct(ServiceRepository $serviceRepository)
    {
        $this->serviceRepository = $serviceRepository;
    }

    /**
     * @param EditServiceCommand $command
     * @throws InvalidArgumentException
     * @throws EntityNotFoundException
     */
    public function handle(EditServiceCommand $command)
    {
        $service = $this->serviceRepository->findById($command->getId());

        if (is_null($service)) {
            throw new EntityNotFoundException('The requested Service cannot be found');
        }

        $service->setName($command->getName());
        $service->setGuid($command->getGuid());
        $service->setTeamName($command->getTeamName());
        $service->setProductionEntitiesEnabled($command->isProductionEntitiesEnabled());
        $service->setPrivacyQuestionsEnabled($command->isPrivacyQuestionsEnabled());

        $service->setServiceType($command->getServiceType());
        $service->setContractSigned($command->getContractSigned());
        $service->setIntakeStatus($command->getIntakeStatus());
        $service->setSurfconextRepresentativeApproved($command->getSurfconextRepresentativeApproved());

        $this->serviceRepository->isUnique($service);

        $this->serviceRepository->save($service);
    }
}
