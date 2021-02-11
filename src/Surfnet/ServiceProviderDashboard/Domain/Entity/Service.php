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

namespace Surfnet\ServiceProviderDashboard\Domain\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @package Surfnet\ServiceProviderDashboard\Entity
 *
 * @ORM\Entity(
 *     repositoryClass="Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Repository\ServiceRepository"
 * )
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Service
{
    // Reflect the type of service either an institute or a 'non institute'
    const SERVICE_TYPE_INSTITUTE = 'institute';
    const SERVICE_TYPE_NON_INSTITUTE = 'non-institute';

    // Has the intake been taken yet?
    const INTAKE_STATUS_YES = 'yes';
    const INTAKE_STATUS_NO = 'no';
    const INTAKE_STATUS_NOT_APPLICABLE = 'not-applicable';

    // For service type: institute: was the contract signed? Yes/No (or null if non institute)
    const CONTRACT_SIGNED_YES = 'yes';
    const CONTRACT_SIGNED_NO = 'no';

    // For service type: institute: did the SURFconext representative approve (null if non institute)
    const SURFCONEXT_APPROVED_YES = 'yes';
    const SURFCONEXT_APPROVED_NO = 'no';

    // Production connection status: (not-requested|requested|active)
    const CONNECTION_STATUS_NOT_REQUESTED = 'not-requested';
    const CONNECTION_STATUS_REQUESTED = 'requested';
    const CONNECTION_STATUS_ACTIVE = 'active';

    const ENTITY_PUBLISHED_NO = 'no';
    const ENTITY_PUBLISHED_IN_PROGRESS = 'in-progress';
    const ENTITY_PUBLISHED_YES = 'yes';

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="guid", nullable=true)
     */
    private $guid;

    /**
     * @var string
     *
     * @ORM\Column(length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(length=255)
     */
    private $teamName;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $productionEntitiesEnabled = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $privacyQuestionsEnabled = true;

    /**
     * @var string
     * @ORM\Column(length=50)
     */
    private $serviceType = self::SERVICE_TYPE_INSTITUTE;

    /**
     * @var string
     * @ORM\Column(length=50)
     */
    private $intakeStatus = self::INTAKE_STATUS_NO;

    /**
     * @var string
     * @ORM\Column(length=50, nullable=true)
     */
    private $contractSigned;

    /**
     * @var string
     * @ORM\Column(length=50, nullable=true)
     */
    private $surfconextRepresentativeApproved;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Contact", mappedBy="services", cascade={"persist"}, orphanRemoval=true)
     */
    private $contacts;

    /**
     * @var PrivacyQuestions
     *
     * @ORM\OneToOne(targetEntity="PrivacyQuestions", mappedBy="service", cascade={"remove"}, orphanRemoval=true)
     */
    private $privacyQuestions;

    /**
     * @var string
     *
     * @ORM\Column(length=255, nullable=true)
     */
    private $institutionId;

    /**
     * @var string
     *
     * @ORM\Column(length=255, nullable=false)
     */
    private $organizationDisplayNameNl;

    /**
     * @var string
     *
     * @ORM\Column(length=255, nullable=false)
     */
    private $organizationDisplayNameEn;

    /**
     * @var string
     *
     * @ORM\Column(length=255, nullable=false)
     */
    private $organizationNameNl;

    /**
     * @var string
     *
     * @ORM\Column(length=255, nullable=false)
     */
    private $organizationNameEn;

    public function __construct()
    {
        $this->contacts = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getTeamName()
    {
        return $this->teamName;
    }

    /**
     * @param string $guid
     */
    public function setGuid($guid)
    {
        $this->guid = $guid;
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
     * @param string $teamName
     */
    public function setTeamName($teamName)
    {
        $this->teamName = $teamName;
    }

    /**
     * @return PrivacyQuestions
     */
    public function getPrivacyQuestions()
    {
        return $this->privacyQuestions;
    }

    /**
     * @param PrivacyQuestions $privacyQuestions
     */
    public function setPrivacyQuestions(PrivacyQuestions $privacyQuestions)
    {
        $this->privacyQuestions = $privacyQuestions;
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
     * @param Contact $contact
     * @return Service
     */
    public function removeContact(Contact $contact)
    {
        $this->contacts->removeElement($contact);

        return $this;
    }

    /**
     * @return string
     */
    public function getServiceType()
    {
        return $this->serviceType;
    }

    /**
     * @param string $serviceType
     */
    public function setServiceType($serviceType)
    {
        $this->serviceType = $serviceType;
    }

    /**
     * @return string
     */
    public function getIntakeStatus()
    {
        return $this->intakeStatus;
    }

    /**
     * @param string $intakeStatus
     */
    public function setIntakeStatus($intakeStatus)
    {
        $this->intakeStatus = $intakeStatus;
    }

    /**
     * @return string
     */
    public function getContractSigned()
    {
        return $this->contractSigned;
    }

    /**
     * @param string $contractSigned
     */
    public function setContractSigned($contractSigned)
    {
        $this->contractSigned = $contractSigned;
    }

    /**
     * @return string
     */
    public function getSurfconextRepresentativeApproved()
    {
        return $this->surfconextRepresentativeApproved;
    }

    /**
     * @param string $surfconextRepresentativeApproved
     */
    public function setSurfconextRepresentativeApproved($surfconextRepresentativeApproved)
    {
        $this->surfconextRepresentativeApproved = $surfconextRepresentativeApproved;
    }

    public function getInstitutionId()
    {
        return $this->institutionId;
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
    public function getOrganizationDisplayNameNl(): string
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
    public function getOrganizationDisplayNameEn(): string
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
    public function getOrganizationNameNl(): string
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
    public function getOrganizationNameEn(): string
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
