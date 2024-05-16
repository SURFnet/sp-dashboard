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

namespace Surfnet\ServiceProviderDashboard\Application\Command\Service;

use Surfnet\ServiceProviderDashboard\Application\Command\Command;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Validator\Constraints as SpDashboardAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class EditServiceCommand implements Command
{
    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList) - Could be decomposed, but for now makes no sense.
     */
    public function __construct(
        private int $id,
        #[Assert\Uuid(strict: false)]
        private string $guid,
        #[Assert\NotBlank]
        private string $name,
        #[SpDashboardAssert\ExistingTeamName]
        #[SpDashboardAssert\UrnFormattedTeamName]
        #[Assert\NotBlank]
        private string $teamName,
        private bool $privacyQuestionsEnabled,
        private bool $clientCredentialClientsEnabled,
        #[Assert\NotBlank]
        private ?string $serviceType,
        #[Assert\NotBlank]
        private ?string $intakeStatus,
        private ?string $contractSigned,
        private ?string $surfconextRepresentativeApproved,
        private bool $privacyQuestionsAnswered,
        private ?string $institutionId,
        #[Assert\NotBlank]
        private ?string $organizationNameNl,
        #[Assert\NotBlank]
        private ?string $organizationNameEn,
    ) {
    }

    public function setGuid(string $guid): void
    {
        $this->guid = $guid;
    }

    public function setTeamName(string $teamName): void
    {
        $this->teamName = $teamName;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setPrivacyQuestionsEnabled(bool $privacyQuestionsEnabled): void
    {
        $this->privacyQuestionsEnabled = $privacyQuestionsEnabled;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @param string $serviceType
     */
    public function setServiceType(?string $serviceType): void
    {
        $this->serviceType = $serviceType;
    }

    /**
     * @param string $intakeStatus
     */
    public function setIntakeStatus(?string $intakeStatus): void
    {
        $this->intakeStatus = $intakeStatus;
    }

    /**
     * @param string $contractSigned
     */
    public function setContractSigned(?string $contractSigned): void
    {
        $this->contractSigned = $contractSigned;
    }

    /**
     * @param string $surfconextRepresentativeApproved
     */
    public function setSurfconextRepresentativeApproved(?string $surfconextRepresentativeApproved): void
    {
        $this->surfconextRepresentativeApproved = $surfconextRepresentativeApproved;
    }

    public function setPrivacyQuestionsAnswered(bool $privacyQuestionsAnswered): void
    {
        $this->privacyQuestionsAnswered = $privacyQuestionsAnswered;
    }

    /**
     * @param string $institutionId
     */
    public function setInstitutionId(?string $institutionId): void
    {
        $this->institutionId = $institutionId;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getGuid(): string
    {
        return $this->guid;
    }

    public function getTeamName(): string
    {
        return $this->teamName;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getServiceType(): ?string
    {
        return $this->serviceType;
    }

    /**
     * @return string
     */
    public function getIntakeStatus(): ?string
    {
        return $this->intakeStatus;
    }

    /**
     * @return string
     */
    public function getContractSigned(): ?string
    {
        return $this->contractSigned;
    }

    /**
     * @return string
     */
    public function getSurfconextRepresentativeApproved(): ?string
    {
        return $this->surfconextRepresentativeApproved;
    }

    /**
     * @return bool
     */
    public function isPrivacyQuestionsAnswered(): bool
    {
        return $this->privacyQuestionsAnswered;
    }

    public function isPrivacyQuestionsEnabled(): bool
    {
        return $this->privacyQuestionsEnabled;
    }

    /**
     * @return string
     */
    public function getInstitutionId(): ?string
    {
        return $this->institutionId;
    }

    /**
     * @return string
     */
    public function getOrganizationNameNl(): ?string
    {
        return $this->organizationNameNl;
    }

    public function setOrganizationNameNl(string $organizationNameNl): void
    {
        $this->organizationNameNl = $organizationNameNl;
    }

    /**
     * @return string
     */
    public function getOrganizationNameEn(): ?string
    {
        return $this->organizationNameEn;
    }

    public function setOrganizationNameEn(string $organizationNameEn): void
    {
        $this->organizationNameEn = $organizationNameEn;
    }

    public function isClientCredentialClientsEnabled(): bool
    {
        return $this->clientCredentialClientsEnabled;
    }

    public function setClientCredentialClientsEnabled(bool $clientCredentialClientsEnabled): void
    {
        $this->clientCredentialClientsEnabled = $clientCredentialClientsEnabled;
    }
}
