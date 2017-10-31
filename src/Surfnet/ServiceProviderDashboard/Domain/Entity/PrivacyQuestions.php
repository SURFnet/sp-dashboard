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

/**
 * @ORM\Entity(
 *     repositoryClass="Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Repository\PrivacyQuestionsRepository"
 * )
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.UnusedPrivateField)
 */
class PrivacyQuestions
{
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
     * @ORM\Column(type="text", nullable=true)
     */
    private $whatData;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $accessData;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $country;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $securityMeasures;

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $certification;

    /**
     * Where can an institution find/request the certificate?
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $certificationLocation;

    /**
     * @var string
     * @ORM\Column(type="date", nullable=true)
     */
    private $certificationValidFrom;

    /**
     * @var string
     * @ORM\Column(type="date", nullable=true)
     */
    private $certificationValidTo;

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $surfmarketDpaAgreement;

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $surfnetDpaAgreement;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $snDpaWhyNot;

    /**
     * @var string
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $privacyPolicy;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $privacyPolicyUrl;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $otherInfo;

    /**
     * @var Service
     *
     * @ORM\OneToOne(targetEntity="Service", inversedBy="privacyQuestions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $service;

    public function setService(Service $service)
    {
        $this->service = $service;
    }

    /**
     * @param string $country
     */
    public function setCountry($country)
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
     * @return bool
     */
    public function isCertified()
    {
        return $this->certification;
    }

    /**
     * @return string
     */
    public function getCertificationLocation()
    {
        return $this->certificationLocation;
    }

    /**
     * @return string
     */
    public function getCertificationValidFrom()
    {
        return $this->certificationValidFrom;
    }

    /**
     * @return string
     */
    public function getCertificationValidTo()
    {
        return $this->certificationValidTo;
    }

    /**
     * @return bool
     */
    public function isSurfmarketDpaAgreement()
    {
        return $this->surfmarketDpaAgreement;
    }

    /**
     * @return bool
     */
    public function isSurfnetDpaAgreement()
    {
        return $this->surfnetDpaAgreement;
    }

    /**
     * @return string
     */
    public function getSnDpaWhyNot()
    {
        return $this->snDpaWhyNot;
    }

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getPrivacyPolicy()
    {
        return $this->privacyPolicy;
    }

    /**
     * @return string
     */
    public function getPrivacyPolicyUrl()
    {
        return $this->privacyPolicyUrl;
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
    public function getService()
    {
        return $this->service;
    }

    /**
     * @param string $whatData
     */
    public function setWhatData($whatData)
    {
        $this->whatData = $whatData;
    }

    /**
     * @param string $accessData
     */
    public function setAccessData($accessData)
    {
        $this->accessData = $accessData;
    }

    /**
     * @param string $securityMeasures
     */
    public function setSecurityMeasures($securityMeasures)
    {
        $this->securityMeasures = $securityMeasures;
    }

    /**
     * @param bool $certification
     */
    public function setCertification($certification)
    {
        $this->certification = $certification;
    }

    /**
     * @param string $certificationLocation
     */
    public function setCertificationLocation($certificationLocation)
    {
        $this->certificationLocation = $certificationLocation;
    }

    /**
     * @param string $certificationValidFrom
     */
    public function setCertificationValidFrom($certificationValidFrom)
    {
        $this->certificationValidFrom = $certificationValidFrom;
    }

    /**
     * @param string $certificationValidTo
     */
    public function setCertificationValidTo($certificationValidTo)
    {
        $this->certificationValidTo = $certificationValidTo;
    }

    /**
     * @param bool $surfmarketDpaAgreement
     */
    public function setSurfmarketDpaAgreement($surfmarketDpaAgreement)
    {
        $this->surfmarketDpaAgreement = $surfmarketDpaAgreement;
    }

    /**
     * @param bool $surfnetDpaAgreement
     */
    public function setSurfnetDpaAgreement($surfnetDpaAgreement)
    {
        $this->surfnetDpaAgreement = $surfnetDpaAgreement;
    }

    /**
     * @param string $snDpaWhyNot
     */
    public function setSnDpaWhyNot($snDpaWhyNot)
    {
        $this->snDpaWhyNot = $snDpaWhyNot;
    }

    /**
     * @param bool $privacyPolicy
     */
    public function setPrivacyPolicy($privacyPolicy)
    {
        $this->privacyPolicy = $privacyPolicy;
    }

    /**
     * @param string $privacyPolicyUrl
     */
    public function setPrivacyPolicyUrl($privacyPolicyUrl)
    {
        $this->privacyPolicyUrl = $privacyPolicyUrl;
    }

    /**
     * @param string $otherInfo
     */
    public function setOtherInfo($otherInfo)
    {
        $this->otherInfo = $otherInfo;
    }
}
