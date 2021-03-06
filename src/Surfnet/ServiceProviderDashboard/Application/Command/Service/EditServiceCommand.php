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

namespace Surfnet\ServiceProviderDashboard\Application\Command\Service;

use Surfnet\ServiceProviderDashboard\Application\Command\Command;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class EditServiceCommand implements Command
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     * @Assert\Uuid(strict=false)
     */
    private $guid;

    /**
     * @var string
     * @Assert\NotBlank
     */
    private $teamName;

    /**
     * @var string
     * @Assert\NotBlank
     */
    private $name;

    /**
     * @var bool
     */
    private $productionEntitiesEnabled = true;

    /**
     * @var bool
     */
    private $privacyQuestionsEnabled = true;

    /**
     * @var bool
     */
    private $clientCredentialClientsEnabled = false;

    /**
     * @var string
     * @Assert\NotBlank
     */
    private $serviceType;

    /**
     * @var string
     * @Assert\NotBlank
     */
    private $intakeStatus;

    /**
     * @var string
     */
    private $contractSigned;

    /**
     * @var string
     */
    private $surfconextRepresentativeApproved;

    /**
     * @var string
     */
    private $privacyQuestionsAnswered;

    /**
     * @var string
     */
    private $institutionId;

    /**
     * @var string
     * @Assert\NotBlank
     */
    private $organizationNameNl;

    /**
     * @var string
     * @Assert\NotBlank
     */
    private $organizationNameEn;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList) - Could be decomposed, but for now makes no sense.
     */
    public function __construct(
        int $id,
        string $guid,
        string $name,
        string $teamName,
        bool $productionEntitiesEnabled,
        bool $privacyQuestionsEnabled,
        bool $clientCredentialClientsEnabled,
        ?string $serviceType,
        ?string $intakeStatus,
        ?string $contractSigned,
        ?string $surfconextRepresentativeApproved,
        ?string $privacyQuestionsAnswered,
        ?string $institutionId,
        ?string $organizationNameNl,
        ?string $organizationNameEn
    ) {
        $this->id = $id;
        $this->guid = $guid;
        $this->name = $name;
        $this->teamName = $teamName;
        $this->productionEntitiesEnabled = $productionEntitiesEnabled;
        $this->privacyQuestionsEnabled = $privacyQuestionsEnabled;
        $this->clientCredentialClientsEnabled = $clientCredentialClientsEnabled;
        $this->serviceType = $serviceType;
        $this->intakeStatus = $intakeStatus;
        $this->contractSigned = $contractSigned;
        $this->surfconextRepresentativeApproved = $surfconextRepresentativeApproved;
        $this->privacyQuestionsAnswered = $privacyQuestionsAnswered;
        $this->institutionId = $institutionId;
        $this->organizationNameEn = $organizationNameEn;
        $this->organizationNameNl = $organizationNameNl;
    }

    /**
     * @param string $guid
     */
    public function setGuid($guid)
    {
        $this->guid = $guid;
    }

    /**
     * @param string $teamName
     */
    public function setTeamName($teamName)
    {
        $this->teamName = $teamName;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param bool $enabled
     */
    public function setProductionEntitiesEnabled($enabled)
    {
        $this->productionEntitiesEnabled = $enabled;
    }

    /**
     * @param bool $privacyQuestionsEnabled
     */
    public function setPrivacyQuestionsEnabled($privacyQuestionsEnabled)
    {
        $this->privacyQuestionsEnabled = $privacyQuestionsEnabled;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param string $serviceType
     */
    public function setServiceType($serviceType)
    {
        $this->serviceType = $serviceType;
    }

    /**
     * @param string $intakeStatus
     */
    public function setIntakeStatus($intakeStatus)
    {
        $this->intakeStatus = $intakeStatus;
    }

    /**
     * @param string $contractSigned
     */
    public function setContractSigned($contractSigned)
    {
        $this->contractSigned = $contractSigned;
    }

    /**
     * @param string $surfconextRepresentativeApproved
     */
    public function setSurfconextRepresentativeApproved($surfconextRepresentativeApproved)
    {
        $this->surfconextRepresentativeApproved = $surfconextRepresentativeApproved;
    }

    /**
     * @param bool $privacyQuestionsAnswered
     */
    public function setPrivacyQuestionsAnswered($privacyQuestionsAnswered)
    {
        $this->privacyQuestionsAnswered = $privacyQuestionsAnswered;
    }

    /**
     * @param string $institutionId
     */
    public function setInstitutionId($institutionId)
    {
        $this->institutionId = $institutionId;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
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
     * @return string
     */
    public function getServiceType()
    {
        return $this->serviceType;
    }

    /**
     * @return string
     */
    public function getIntakeStatus()
    {
        return $this->intakeStatus;
    }

    /**
     * @return string
     */
    public function getContractSigned()
    {
        return $this->contractSigned;
    }

    /**
     * @return string
     */
    public function getSurfconextRepresentativeApproved()
    {
        return $this->surfconextRepresentativeApproved;
    }

    /**
     * @return bool
     */
    public function isPrivacyQuestionsAnswered()
    {
        return $this->privacyQuestionsAnswered;
    }

    /**
     * @return bool
     */
    public function isProductionEntitiesEnabled()
    {
        return $this->productionEntitiesEnabled;
    }

    /**
     * @return bool
     */
    public function isPrivacyQuestionsEnabled()
    {
        return $this->privacyQuestionsEnabled;
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

    /**
     * @param string $organizationNameNl
     */
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

    /**
     * @param string $organizationNameEn
     */
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
