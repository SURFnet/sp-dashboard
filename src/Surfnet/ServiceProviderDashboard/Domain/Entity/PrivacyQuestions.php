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
    #[ORM\Id]
    #[ORM\Column(type: 'integer', options: ['autoincrement' => true])]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $whatData = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $accessData = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $country = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $securityMeasures = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $otherInfo = null;

    
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

    public function setCountry(?string $country): void
    {
        $this->country = $country;
    }

    public function getWhatData(): ?string
    {
        return $this->whatData;
    }

    public function getAccessData(): ?string
    {
        return $this->accessData;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function getSecurityMeasures(): ?string
    {
        return $this->securityMeasures;
    }

    public function getOtherInfo(): ?string
    {
        return $this->otherInfo;
    }

    public function getService(): ?Service
    {
        return $this->service;
    }

    public function setWhatData(?string $whatData): void
    {
        $this->whatData = $whatData;
    }

    public function setAccessData(?string $accessData): void
    {
        $this->accessData = $accessData;
    }

    public function setSecurityMeasures(?string $securityMeasures): void
    {
        $this->securityMeasures = $securityMeasures;
    }

    public function setOtherInfo(?string $otherInfo): void
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

    /**
     * @return array<string, string>
     */
    public function privacyStatementUrls(): array
    {
        $out = [];
        if ($this->privacyStatementUrlEn !== '0' && $this->privacyStatementUrlEn !== null) {
            $out['mdui:PrivacyStatementURL:en'] = $this->privacyStatementUrlEn;
        }
        if ($this->privacyStatementUrlNl !== '0' && $this->privacyStatementUrlNl !== null) {
            $out['mdui:PrivacyStatementURL:nl'] = $this->privacyStatementUrlNl;
        }
        return $out;
    }
}
