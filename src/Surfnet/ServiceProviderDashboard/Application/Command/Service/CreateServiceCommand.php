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
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Symfony\Component\Validator\Constraints as Assert;

class CreateServiceCommand implements Command
{
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
     * @var string
     */
    private $institutionId;

    /**
     * @var bool
     */
    private $productionEntitiesEnabled = false;

    /**
     * @var bool
     */
    private $privacyQuestionsEnabled = true;

    /**
     * @var string
     * @Assert\NotBlank
     */
    private $serviceType = Service::SERVICE_TYPE_NON_INSTITUTE;

    /**
     * @var string
     * @Assert\NotBlank
     */
    private $intakeStatus = Service::INTAKE_STATUS_NO;

    /**
     * @var string
     */
    private $contractSigned = Service::CONTRACT_SIGNED_NO;

    /**
     * @var string
     */
    private $surfconextRepresentativeApproved = Service::SURFCONEXT_APPROVED_NO;

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
     * @param string $institutionId
     */
    public function setInstitutionId($institutionId)
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
     * New services have no privacy questions answers yet.
     * @return bool
     */
    public function hasPrivacyQuestionsAnswered()
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
}
