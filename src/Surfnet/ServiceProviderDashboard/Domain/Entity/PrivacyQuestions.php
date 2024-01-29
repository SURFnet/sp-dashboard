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

use Doctrine\ORM\Mapping as ORM;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Repository\PrivacyQuestionsRepository;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.UnusedPrivateField)
 */
#[ORM\Entity(repositoryClass: PrivacyQuestionsRepository::class)]
class PrivacyQuestions
{
    /**
     * @var int
     */
    #[ORM\Id]
    #[ORM\Column(type: 'integer', options: ['autoincrement' => true])]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $whatData;

    /**
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $accessData;

    /**
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $country;

    /**
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $securityMeasures;

    /**
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $otherInfo;

    
    #[ORM\OneToOne(targetEntity: 'Service', inversedBy: 'privacyQuestions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Service $service = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $dpaType = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $privacyStatementUrlNl = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $privacyStatementUrlEn = null;

    public function setService(Service $service): void
    {
        $this->service = $service;
    }

    /**
     * @param string $country
     */
    public function setCountry($country): void
    {
        $this->country = $country;
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
    public function getService(): ?Service
    {
        return $this->service;
    }

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
        $this->dpaType = $dpaType;
    }

    public function getDpaType(): ?string
    {
        return $this->dpaType;
    }

    public function getPrivacyStatementUrlNl(): ?string
    {
        return $this->privacyStatementUrlNl;
    }

    public function setPrivacyStatementUrlNl(?string $privacyStatementUrlNl): void
    {
        $this->privacyStatementUrlNl = $privacyStatementUrlNl;
    }

    public function getPrivacyStatementUrlEn(): ?string
    {
        return $this->privacyStatementUrlEn;
    }

    public function setPrivacyStatementUrlEn(?string $privacyStatementUrlEn): void
    {
        $this->privacyStatementUrlEn = $privacyStatementUrlEn;
    }

    public function privacyStatementUrls(): array
    {
        $out = [];
        if ($this->privacyStatementUrlEn !== '' && $this->privacyStatementUrlEn !== '0') {
            $out['mdui:PrivacyStatementURL:en'] = $this->privacyStatementUrlEn;
        }
        if ($this->privacyStatementUrlNl !== '' && $this->privacyStatementUrlNl !== '0') {
            $out['mdui:PrivacyStatementURL:nl'] = $this->privacyStatementUrlNl;
        }
        return $out;
    }
}
