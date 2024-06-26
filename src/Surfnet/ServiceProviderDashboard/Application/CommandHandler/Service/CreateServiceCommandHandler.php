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
use Surfnet\ServiceProviderDashboard\Domain\Repository\PublishTeamsRepository;
use Surfnet\ServiceProviderDashboard\Domain\Repository\ServiceRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\CreateTeamsException;
use Symfony\Contracts\Translation\TranslatorInterface;

class CreateServiceCommandHandler implements CommandHandler
{
    public function __construct(
        private readonly ServiceRepository $serviceRepository,
        private readonly PublishTeamsRepository $publishEntityClient,
        private readonly TranslatorInterface $translator,
        private readonly string $prefixPart1,
        private readonly string $prefixPart2,
    ) {
    }

    /**
     * @throws CreateTeamsException
     */
    public function handle(CreateServiceCommand $command): void
    {
        $name = $command->getName();
        $teamName = strtolower($command->getTeamName());
        $fullTeamName = $this->prefixPart1 . $this->prefixPart2 . $teamName;

        /**
 * create team
**/
        $team = $this->createTeamData($name, $teamName, $command->getTeamManagerEmail());
        $this->publishEntityClient->createTeam($team);

        /**
 * create service
**/
        $service = new Service();
        $service->setName($name);
        $service->setGuid($command->getGuid());
        $service->setTeamName($fullTeamName);
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
        $this->serviceRepository->save($service);
    }

    private function createTeamData(string $name, string $teamName, string $email): array
    {
        $emails = $this->createEmailsArray($email);

        return [
            'name' => $teamName,
            'description' => $this->translator->trans(
                'teams.create.description',
                [
                '%teamName%' => $name,
                ]
            ),
            'personalNote' => $this->translator->trans('teams.create.personalNote'),
            'viewable' => false,
            'emails' => $emails,
            'roleOfCurrentUser' => 'MANAGER',
            'invitationMessage' => $this->translator->trans('teams.create.invitationMessage'),
            'language' => 'ENGLISH',
        ];
    }

    private function createEmailsArray(string $email): array
    {
        $emails = [];
        foreach (explode(',', $email) as $mail) {
            $emails[trim($mail)] = 'MANAGER';
        }

        return $emails;
    }
}
