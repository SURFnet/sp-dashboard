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
     * @var string
     * @Assert\NotBlank
     */
    private $organizationDisplayNameNl;

    /**
     * @var string
     * @Assert\NotBlank
     */
    private $organizationDisplayNameEn;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList) - Could be decomposed, but for now makes no sense.
     *
     * @param int $id
     * @param string $guid
     * @param string $name
     * @param string $teamName
     * @param bool $productionEntitiesEnabled
     * @param bool $privacyQuestionsEnabled
     * @param string $serviceType
     * @param string $intakeStatus
     * @param string $contractSigned
     * @param string $surfconextRepresentativeApproved
     * @param string $privacyQuestionsAnswered
     * @param string $institutionId
     * @param string $organizationNameNl
     * @param string $organizationNameEn
     * @param string $organizationDisplayNameNl
     * @param string $organizationDisplayNameEn
     */
    public function __construct(
        $id,
        $guid,
        $name,
        $teamName,
        $productionEntitiesEnabled,
        $privacyQuestionsEnabled,
        $serviceType,
        $intakeStatus,
        $contractSigned,
        $surfconextRepresentativeApproved,
        $privacyQuestionsAnswered,
        $institutionId,
        $organizationNameNl,
        $organizationNameEn,
        $organizationDisplayNameNl,
        $organizationDisplayNameEn
    ) {
        $this->id = $id;
        $this->guid = $guid;
        $this->name = $name;
        $this->teamName = $teamName;
        $this->productionEntitiesEnabled = $productionEntitiesEnabled;
        $this->privacyQuestionsEnabled = $privacyQuestionsEnabled;
        $this->serviceType = $serviceType;
        $this->intakeStatus = $intakeStatus;
        $this->contractSigned = $contractSigned;
        $this->surfconextRepresentativeApproved = $surfconextRepresentativeApproved;
        $this->privacyQuestionsAnswered = $privacyQuestionsAnswered;
        $this->institutionId = $institutionId;
        $this->organizationNameEn = $organizationNameEn;
        $this->organizationNameNl = $organizationNameNl;
        $this->organizationDisplayNameEn = $organizationDisplayNameEn;
        $this->organizationDisplayNameNl = $organizationDisplayNameNl;
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
    public function getOrganizationDisplayNameNl(): ?string
    {
        return $this->organizationDisplayNameNl;
    }

    /**
     * @param string $organizationDisplayNameNl
     */
    public function setOrganizationDisplayNameNl(string $organizationDisplayNameNl): void
    {
        $this->organizationDisplayNameNl = $organizationDisplayNameNl;
    }

    /**
     * @return string
     */
    public function getOrganizationDisplayNameEn(): ?string
    {
        return $this->organizationDisplayNameEn;
    }

    /**
     * @param string $organizationDisplayNameEn
     */
    public function setOrganizationDisplayNameEn(string $organizationDisplayNameEn): void
    {
        $this->organizationDisplayNameEn = $organizationDisplayNameEn;
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
}
