<?php

/**
 * Copyright 2019 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Application\Command\Entity;

use InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Contact;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Validator\Constraints as SpDashboardAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class SaveOidcngResourceServerEntityCommand implements SaveEntityCommandInterface
{
    /**
     * @var string
     */
    #[Assert\Uuid]
    private $id;

    /**
     * @var string
     */
    private $status;

    private ?\Surfnet\ServiceProviderDashboard\Domain\Entity\Service $service = null;

    private bool $archived = false;

    
    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['production', 'test'], strict: true)]
    private string $environment = Constants::ENVIRONMENT_TEST;

    /**
     *
     * @SpDashboardAssert\ValidClientId()
     * @SpDashboardAssert\UniqueEntityId()
     */
    #[Assert\NotBlank]
    private ?string $entityId = null;

    /**
     * @var string
     */
    private $secret;

    /**
     * @var string
     */
    #[Assert\NotBlank]
    private $nameNl;

    /**
     * @var string
     */
    #[Assert\NotBlank]
    private $nameEn;

    /**
     * @var string
     */
    #[Assert\NotBlank]
    #[Assert\Length(max: 300)]
    private $descriptionNl;

    /**
     * @var string
     */
    #[Assert\NotBlank]
    #[Assert\Length(max: 300)]
    private $descriptionEn;

    /**
     * @var Contact
     */
    #[Assert\Type(type: \Surfnet\ServiceProviderDashboard\Domain\ValueObject\Contact::class)]
    #[Assert\Valid(groups: ['production'])]
    private $administrativeContact;

    /**
     * @var Contact
     */
    #[Assert\Type(type: \Surfnet\ServiceProviderDashboard\Domain\ValueObject\Contact::class)]
    #[Assert\Valid]
    private $technicalContact;

    /**
     * @var Contact
     */
    #[Assert\Type(type: \Surfnet\ServiceProviderDashboard\Domain\ValueObject\Contact::class)]
    #[Assert\Valid(groups: ['production'])]
    private $supportContact;

    /**
     * @var string
     */
    private $comments;

    /**
     * @var string
     */
    private $manageId;

    private ?bool $isCopy = null;

    /**
     * @var string[]
     */
    private array $scopes = ['openid'];

    /**
     * @return SaveOidcngResourceServerEntityCommand
     */
    public static function forCreateAction(Service $service, bool $isCopy = false): SaveOidcngResourceServerEntityCommand
    {
        $command = new self();
        $command->service = $service;
        $command->isCopy = $isCopy;

        return $command;
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
    public function getStatus()
    {
        return $this->status;
    }

    public function getService(): Service
    {
        return $this->service;
    }

    /**
     * @return bool
     */
    public function isCopy(): ?bool
    {
        return $this->isCopy;
    }

    public function setIsCopy(bool $isCopy): void
    {
        $this->isCopy = $isCopy;
    }

    /**
     * @return bool
     */
    public function isArchived(): bool
    {
        return $this->archived;
    }

    public function setArchived(bool $archived): void
    {
        $this->archived = $archived;
    }

    public function getEnvironment(): ?string
    {
        return $this->environment;
    }

    /**
     * @param string $environment
     */
    public function setEnvironment($environment): void
    {
        if (!in_array(
            $environment,
            [
                Constants::ENVIRONMENT_TEST,
                Constants::ENVIRONMENT_PRODUCTION,
            ]
        )) {
            throw new InvalidArgumentException(
                "Unknown environment '{$environment}'"
            );
        }

        $this->environment = $environment;
    }

    public function getEntityId(): ?string
    {
        return $this->entityId;
    }

    /**
     * @param string $entityId
     */
    public function setEntityId($entityId): void
    {
        $this->entityId = strtolower($entityId);
    }

    /**
     * @return string
     */
    public function getClientId(): ?string
    {
        return $this->entityId;
    }

    /**
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * @param string $secret
     */
    public function setSecret($secret): void
    {
        $this->secret = $secret;
    }

    public function getNameNl(): ?string
    {
        return $this->nameNl;
    }

    /**
     * @param string $nameNl
     */
    public function setNameNl($nameNl): void
    {
        $this->nameNl = $nameNl;
    }

    public function getNameEn(): ?string
    {
        return $this->nameEn;
    }

    /**
     * @param string $nameEn
     */
    public function setNameEn($nameEn): void
    {
        $this->nameEn = $nameEn;
    }

    public function getDescriptionNl(): ?string
    {
        return $this->descriptionNl;
    }

    /**
     * @param string $descriptionNl
     */
    public function setDescriptionNl($descriptionNl): void
    {
        $this->descriptionNl = $descriptionNl;
    }

    /**
     * @return string
     */
    public function getDescriptionEn(): ?string
    {
        return $this->descriptionEn;
    }

    /**
     * @param string $descriptionEn
     */
    public function setDescriptionEn($descriptionEn): void
    {
        $this->descriptionEn = $descriptionEn;
    }

    /**
     * @return Contact
     */
    public function getAdministrativeContact(): ?Contact
    {
        return $this->administrativeContact;
    }

    /**
     * @param Contact $administrativeContact
     */
    public function setAdministrativeContact($administrativeContact): void
    {
        $this->administrativeContact = $administrativeContact;
    }

    /**
     * @return Contact
     */
    public function getTechnicalContact(): ?Contact
    {
        return $this->technicalContact;
    }

    /**
     * @param Contact $technicalContact
     */
    public function setTechnicalContact($technicalContact): void
    {
        $this->technicalContact = $technicalContact;
    }

    /**
     * @return Contact
     */
    public function getSupportContact(): ?Contact
    {
        return $this->supportContact;
    }

    /**
     * @param Contact $supportContact
     */
    public function setSupportContact($supportContact): void
    {
        $this->supportContact = $supportContact;
    }

     /**
     * @return string
     */
    public function getComments(): ?string
    {
        return $this->comments;
    }

    /**
     * @param string $comments
     */
    public function setComments($comments): void
    {
        $this->comments = $comments;
    }

    public function isForProduction(): bool
    {
        return $this->environment === Constants::ENVIRONMENT_PRODUCTION;
    }

    public function setId($id): void
    {
        $this->id = $id;
    }

    public function setStatus($status): void
    {
        $this->status = $status;
    }

    public function setService(Service $service): void
    {
        $this->service = $service;
    }

    /**
     * @return string
     */
    public function getManageId()
    {
        return $this->manageId;
    }

    /**
     * @param string $manageId
     */
    public function setManageId($manageId): void
    {
        $this->manageId = $manageId;
    }

    public function getProtocol(): string
    {
        return Constants::TYPE_OPENID_CONNECT_TNG_RESOURCE_SERVER;
    }

    /**
     * @return array
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }

    public function getMetadataUrl(): ?string
    {
        return null;
    }

    public function getAcsLocations(): ?array
    {
        return null;
    }

    public function getNameIdFormat(): ?string
    {
        return null;
    }

    public function getApplicationUrl(): ?string
    {
        return null;
    }

    public function getEulaUrl(): ?string
    {
        return null;
    }

    public function getCertificate(): ?string
    {
        return null;
    }

    public function getLogoUrl(): ?string
    {
        return null;
    }
}
