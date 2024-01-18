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
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Validator\Constraints as SpDashboardAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class CreateServiceCommand implements Command
{
    /**
     * @var string
     */
    #[Assert\Uuid(strict: false)]
    private $guid;

    /**
     * @var                                string
     * @SpDashboardAssert\UniqueTeamName()
     */
    #[Assert\NotBlank]
    private $teamName;

    /**
     * @var string
     */
    #[Assert\NotBlank]
    private $name;

    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $teamManagerEmail = null;

    /**
     * @var string
     */
    private $institutionId;

    private bool $productionEntitiesEnabled = false;

    private bool $privacyQuestionsEnabled = true;

    private bool $clientCredentialClientsEnabled = false;

    #[Assert\NotBlank]
    private string $serviceType = Service::SERVICE_TYPE_NON_INSTITUTE;

    #[Assert\NotBlank]
    private string $intakeStatus = Service::INTAKE_STATUS_NO;

    private string $contractSigned = Service::CONTRACT_SIGNED_NO;

    private string $surfconextRepresentativeApproved = Service::SURFCONEXT_APPROVED_NO;

    #[Assert\NotBlank]
    private ?string $organizationNameNl = null;

    #[Assert\NotBlank]
    private ?string $organizationNameEn = null;

    /**
     * @param string $guid
     */
    public function setGuid($guid): void
    {
        $this->guid = $guid;
    }

    /**
     * @param string $teamName
     */
    public function setTeamName($teamName): void
    {
        $this->teamName = $teamName;
    }

    /**
     * @param string $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    public function setProductionEntitiesEnabled(bool $enabled): void
    {
        $this->productionEntitiesEnabled = $enabled;
    }

    public function setPrivacyQuestionsEnabled(bool $privacyQuestionsEnabled): void
    {
        $this->privacyQuestionsEnabled = $privacyQuestionsEnabled;
    }

    public function setServiceType(string $serviceType): void
    {
        $this->serviceType = $serviceType;
    }

    public function setIntakeStatus(string $intakeStatus): void
    {
        $this->intakeStatus = $intakeStatus;
    }

    public function setContractSigned(string $contractSigned): void
    {
        $this->contractSigned = $contractSigned;
    }

    public function setSurfconextRepresentativeApproved(string $surfconextRepresentativeApproved): void
    {
        $this->surfconextRepresentativeApproved = $surfconextRepresentativeApproved;
    }

    /**
     * @param string $institutionId
     */
    public function setInstitutionId($institutionId): void
    {
        $this->institutionId = $institutionId;
    }

    /**
     * @return string
     */
    public function getGuid()
    {
        return $this->guid;
    }

    /**
     * @return string
     */
    public function getTeamName()
    {
        return $this->teamName;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isProductionEntitiesEnabled(): bool
    {
        return $this->productionEntitiesEnabled;
    }

    /**
     * @return bool
     */
    public function isPrivacyQuestionsEnabled(): bool
    {
        return $this->privacyQuestionsEnabled;
    }

    /**
     * @return string
     */
    public function getServiceType(): string
    {
        return $this->serviceType;
    }

    /**
     * @return string
     */
    public function getIntakeStatus(): string
    {
        return $this->intakeStatus;
    }

    /**
     * @return string
     */
    public function getContractSigned(): string
    {
        return $this->contractSigned;
    }

    /**
     * @return string
     */
    public function getSurfconextRepresentativeApproved(): string
    {
        return $this->surfconextRepresentativeApproved;
    }

    /**
     * New services have no privacy questions answers yet.
     *
     * @return bool
     */
    public function hasPrivacyQuestionsAnswered(): bool
    {
        return false;
    }

    /**
     * @return string
     */
    public function getInstitutionId()
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

    public function getTeamManagerEmail(): ?string
    {
        return $this->teamManagerEmail;
    }

    public function setTeamManagerEmail(string $teamManagerEmail): void
    {
        $this->teamManagerEmail = $teamManagerEmail;
    }
}
