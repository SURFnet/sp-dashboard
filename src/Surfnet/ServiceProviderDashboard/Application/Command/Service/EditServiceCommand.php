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

class EditServiceCommand implements Command
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank
     * @Assert\Uuid
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
    private $privacyQuestionsEnabled = true;

    /**
     * @param int $id
     * @param string $guid
     * @param string $name
     * @param string $teamName
     * @param $privacyQuestionsEnabled
     */
    public function __construct($id, $guid, $name, $teamName, $privacyQuestionsEnabled)
    {
        $this->id = $id;
        $this->guid = $guid;
        $this->name = $name;
        $this->teamName = $teamName;
        $this->privacyQuestionsEnabled = $privacyQuestionsEnabled;
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
     * @param bool $privacyQuestionsEnabled
     */
    public function setPrivacyQuestionsEnabled($privacyQuestionsEnabled)
    {
        $this->privacyQuestionsEnabled = $privacyQuestionsEnabled;
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
     * @return bool
     */
    public function isPrivacyQuestionsEnabled()
    {
        return $this->privacyQuestionsEnabled;
    }
}
