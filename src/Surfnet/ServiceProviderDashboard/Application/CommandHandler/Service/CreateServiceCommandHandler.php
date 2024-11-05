<?php

declare(strict_types = 1);

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

use Surfnet\ServiceProviderDashboard\Application\Command\Service\CreateServiceCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\CommandHandler;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Repository\Invite\CreateRoleRepository;
use Surfnet\ServiceProviderDashboard\Domain\Repository\ServiceRepository;
use Symfony\Contracts\Translation\TranslatorInterface;

class CreateServiceCommandHandler implements CommandHandler
{
    public function __construct(
        private readonly ServiceRepository $serviceRepository,
        private readonly CreateRoleRepository $inviteRepository,
        private readonly TranslatorInterface $translator,
        private readonly string $prefixPart1,
        private readonly string $prefixPart2,
        private readonly string $landingUrl,
    ) {
    }

    public function handle(CreateServiceCommand $command): void
    {
        $serviceName = $command->getName();
        $teamName = strtolower($command->getTeamName());
        $fullTeamName = $this->prefixPart1 . $this->prefixPart2 . $teamName;
        $roleDescription = $this->translator->trans(
            'invite.role_create.description',
            [
                '%serviceName%' => $serviceName,
                '%organisationName%' => $command->getOrganizationNameEn()
            ]
        );

        $service = new Service();
        $service->setName($serviceName);
        $service->setGuid($command->getGuid());
        $service->setTeamName($fullTeamName);
        $service->setProductionEntitiesEnabled($command->isProductionEntitiesEnabled());
        $service->setPrivacyQuestionsEnabled($command->isPrivacyQuestionsEnabled());
        $service->setClientCredentialClientsEnabled($command->isClientCredentialClientsEnabled());
        $service->setServiceType($command->getServiceType());
        $service->setContractSigned($command->getContractSigned());
        $service->setIntakeStatus($command->getIntakeStatus());
        $service->setSurfconextRepresentativeApproved($command->getSurfconextRepresentativeApproved());
        $service->setInstitutionId($command->getInstitutionId());
        $service->setOrganizationNameEn($command->getOrganizationNameEn());
        $service->setOrganizationNameNl($command->getOrganizationNameNl());
        $this->serviceRepository->isUnique($service);

        $roleName = sprintf('%s %s', $serviceName, $command->getOrganizationNameEn());

        $response = $this->inviteRepository->createRole(
            $roleName,
            $roleName,
            $roleDescription,
            $this->landingUrl,
            $command->getManageId(),
        );

        $service->registerInvite($response->urn, $response->id);

        $this->serviceRepository->save($service);
        $command->setServiceId($service->getId());
    }
}
