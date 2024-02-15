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
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Surfnet\ServiceProviderDashboard\Domain\Entity\PrivacyQuestions;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Repository\ServiceRepository;

/**
 * @package Surfnet\ServiceProviderDashboard\Entity
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
#[ORM\Entity(repositoryClass: ServiceRepository::class)]
class Service
{
    // Reflect the type of service either an institute or a 'non institute'
    final public const SERVICE_TYPE_INSTITUTE = 'institute';
    final public const SERVICE_TYPE_NON_INSTITUTE = 'non-institute';

    // Has the intake been taken yet?
    final public const INTAKE_STATUS_YES = 'yes';
    final public const INTAKE_STATUS_NO = 'no';
    final public const INTAKE_STATUS_NOT_APPLICABLE = 'not-applicable';

    // For service type: institute: was the contract signed? Yes/No (or null if non institute)
    final public const CONTRACT_SIGNED_YES = 'yes';
    final public const CONTRACT_SIGNED_NO = 'no';

    // For service type: institute: did the SURFconext representative approve (null if non institute)
    final public const SURFCONEXT_APPROVED_YES = 'yes';
    final public const SURFCONEXT_APPROVED_NO = 'no';

    // Production connection status: (not-requested|requested|active)
    final public const CONNECTION_STATUS_NOT_REQUESTED = 'not-requested';
    final public const CONNECTION_STATUS_REQUESTED = 'requested';
    final public const CONNECTION_STATUS_ACTIVE = 'active';

    final public const ENTITY_PUBLISHED_NO = 'no';
    final public const ENTITY_PUBLISHED_IN_PROGRESS = 'in-progress';
    final public const ENTITY_PUBLISHED_YES = 'yes';

    /**
     * @var int
     */
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var string
     */
    #[ORM\Column(type: 'guid', nullable: true)]
    private $guid;

    /**
     * @var string
     */
    #[ORM\Column(length: 255)]
    private $name;

    /**
     * @var string
     */
    #[ORM\Column(length: 255)]
    private $teamName;

    #[ORM\Column(type: 'boolean')]
    private bool $productionEntitiesEnabled = false;

    #[ORM\Column(type: 'boolean')]
    private bool $privacyQuestionsEnabled = true;

    #[ORM\Column(type: 'boolean')]
    private bool $clientCredentialClientsEnabled = false;

    #[ORM\Column(length: 50)]
    private string $serviceType = self::SERVICE_TYPE_INSTITUTE;

    #[ORM\Column(length: 50)]
    private string $intakeStatus = self::INTAKE_STATUS_NO;

    /**
     * @var string
     */
    #[ORM\Column(length: 50, nullable: true)]
    private $contractSigned;

    /**
     * @var string
     */
    #[ORM\Column(length: 50, nullable: true)]
    private $surfconextRepresentativeApproved;

    #[ORM\ManyToMany(targetEntity: 'Contact', mappedBy: 'services', cascade: ['persist'], orphanRemoval: true)]
    private Collection $contacts;

    #[ORM\OneToOne(targetEntity: 'PrivacyQuestions', mappedBy: 'service', cascade: ['remove'], orphanRemoval: true)]
    private ?PrivacyQuestions $privacyQuestions = null;

    /**
     * @var string
     */
    #[ORM\Column(length: 255, nullable: true)]
    private $institutionId;

    #[ORM\Column(length: 255, nullable: false)]
    private ?string $organizationNameNl = null;

    #[ORM\Column(length: 255, nullable: false)]
    private ?string $organizationNameEn = null;

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
    public function setId($id): void
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
    public function setGuid($guid): void
    {
        $this->guid = $guid;
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

    /**
     * @param string $teamName
     */
    public function setTeamName($teamName): void
    {
        $this->teamName = $teamName;
    }

    /**
     * @return PrivacyQuestions
     */
    public function getPrivacyQuestions(): ?PrivacyQuestions
    {
        return $this->privacyQuestions;
    }

    public function setPrivacyQuestions(PrivacyQuestions $privacyQuestions): void
    {
        $this->privacyQuestions = $privacyQuestions;
    }

    public function isProductionEntitiesEnabled(): bool
    {
        return $this->productionEntitiesEnabled;
    }

    public function isPrivacyQuestionsEnabled(): bool
    {
        return $this->privacyQuestionsEnabled;
    }

    public function isClientCredentialClientsEnabled(): bool
    {
        return $this->clientCredentialClientsEnabled;
    }

    public function setClientCredentialClientsEnabled(bool $clientCredentialClientsEnabled): void
    {
        $this->clientCredentialClientsEnabled = $clientCredentialClientsEnabled;
    }

    public function removeContact(Contact $contact): static
    {
        $this->contacts->removeElement($contact);

        return $this;
    }

    public function getServiceType(): string
    {
        return $this->serviceType;
    }

    public function setServiceType(string $serviceType): void
    {
        $this->serviceType = $serviceType;
    }

    public function getIntakeStatus(): string
    {
        return $this->intakeStatus;
    }

    public function setIntakeStatus(string $intakeStatus): void
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
    public function setContractSigned($contractSigned): void
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
    public function setSurfconextRepresentativeApproved($surfconextRepresentativeApproved): void
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
    public function setInstitutionId($institutionId): void
    {
        $this->institutionId = $institutionId;
    }

    public function getOrganizationNameNl(): ?string
    {
        return $this->organizationNameNl;
    }

    public function setOrganizationNameNl(string $organizationNameNl): void
    {
        $this->organizationNameNl = $organizationNameNl;
    }

    public function getOrganizationNameEn(): string
    {
        return $this->organizationNameEn;
    }

    public function setOrganizationNameEn(string $organizationNameEn): void
    {
        $this->organizationNameEn = $organizationNameEn;
    }
}
