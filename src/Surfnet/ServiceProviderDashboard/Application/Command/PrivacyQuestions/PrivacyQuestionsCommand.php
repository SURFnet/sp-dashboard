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

use DateTime;
use Surfnet\ServiceProviderDashboard\Application\Command\Command;
use Surfnet\ServiceProviderDashboard\Domain\Entity\PrivacyQuestions;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class PrivacyQuestionsCommand implements Command
{
    const MODE_CREATE = 0;

    const MODE_EDIT = 1;

    /**
     * @var int
     */
    private $mode;
    
    /**
     * @var Service
     */
    private $service;

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
     * @param string $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * @param string $securityMeasures
     */
    public function setSecurityMeasures($securityMeasures)
    {
        $this->securityMeasures = $securityMeasures;
    }

    /**
     * @param string $otherInfo
     */
    public function setOtherInfo($otherInfo)
    {
        $this->otherInfo = $otherInfo;
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
    public function getService()
    {
        return $this->service;
    }

    /**
     * @return int
     */
    public function getMode()
    {
        return $this->mode;
    }

    public static function fromService(Service $service)
    {
        $command = new self;
        $command->mode = self::MODE_CREATE;

        $command->service = $service;
        return $command;
    }

    public static function fromQuestions(PrivacyQuestions $questions)
    {
        $command = new self;
        $command->mode = self::MODE_EDIT;

        $command->accessData = $questions->getAccessData();
        $command->country = $questions->getCountry();
        $command->otherInfo = $questions->getOtherInfo();
        $command->securityMeasures = $questions->getSecurityMeasures();
        $command->service = $questions->getService();
        $command->whatData = $questions->getWhatData();

        return $command;
    }
}
