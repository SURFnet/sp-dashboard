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

namespace Surfnet\ServiceProviderDashboard\Application\Command\PrivacyQuestions;

use Surfnet\ServiceProviderDashboard\Application\Command\Command;
use Surfnet\ServiceProviderDashboard\Domain\Entity\PrivacyQuestions;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\DpaType;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class PrivacyQuestionsCommand implements Command
{
    final public const MODE_CREATE = 0;

    final public const MODE_EDIT = 1;

    private ?int $mode = null;
    
    private ?\Surfnet\ServiceProviderDashboard\Domain\Entity\Service $service = null;

    /**
     * @var string
     */
    private $whatData;

    /**
     * @var string
     */
    private $accessData;

    /**
     * @var string
     */
    private $country;

    /**
     * @var string
     */
    private $securityMeasures;

    /**
     * @var string
     */
    private $otherInfo;

    #[Assert\NotBlank]
    #[Assert\Type(\Surfnet\ServiceProviderDashboard\Domain\ValueObject\DpaType::class)]
    private DpaType $dpaType;

    #[Assert\Url]
    public ?string $privacyStatementUrlNl = '';

    #[Assert\Url]
    public ?string $privacyStatementUrlEn = '';

    /**
     * @param string $whatData
     */
    public function setWhatData($whatData): void
    {
        $this->whatData = $whatData;
    }

    /**
     * @param string $accessData
     */
    public function setAccessData($accessData): void
    {
        $this->accessData = $accessData;
    }

    /**
     * @param string $country
     */
    public function setCountry($country): void
    {
        $this->country = $country;
    }

    /**
     * @param string $securityMeasures
     */
    public function setSecurityMeasures($securityMeasures): void
    {
        $this->securityMeasures = $securityMeasures;
    }

    /**
     * @param string $otherInfo
     */
    public function setOtherInfo($otherInfo): void
    {
        $this->otherInfo = $otherInfo;
    }

    public function setDpaType(string $dpaType): void
    {
        $this->dpaType = DpaType::fromString($dpaType);
    }

    /**
     * @return string
     */
    public function getWhatData()
    {
        return $this->whatData;
    }

    /**
     * @return string
     */
    public function getAccessData()
    {
        return $this->accessData;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @return string
     */
    public function getSecurityMeasures()
    {
        return $this->securityMeasures;
    }

    /**
     * @return string
     */
    public function getOtherInfo()
    {
        return $this->otherInfo;
    }

    /**
     * @return Service
     */
    public function getService(): ?\Surfnet\ServiceProviderDashboard\Domain\Entity\Service
    {
        return $this->service;
    }

    /**
     * @return int
     */
    public function getMode(): ?int
    {
        return $this->mode;
    }

    public function getDpaType(): DpaType
    {
        return $this->dpaType;
    }

    public static function fromService(Service $service): self
    {
        $command = new self;
        $command->mode = self::MODE_CREATE;
        $command->dpaType = DpaType::build(DpaType::DEFAULT);
        $command->service = $service;
        return $command;
    }

    public static function fromQuestions(PrivacyQuestions $questions): self
    {
        $command = new self;
        $command->mode = self::MODE_EDIT;

        $command->accessData = $questions->getAccessData();
        $command->country = $questions->getCountry();
        $command->otherInfo = $questions->getOtherInfo();
        $command->securityMeasures = $questions->getSecurityMeasures();
        $command->service = $questions->getService();
        $command->whatData = $questions->getWhatData();
        $command->dpaType = DpaType::from($questions);
        $command->privacyStatementUrlNl = $questions->getPrivacyStatementUrlNl();
        $command->privacyStatementUrlEn = $questions->getPrivacyStatementUrlEn();
        return $command;
    }
}
