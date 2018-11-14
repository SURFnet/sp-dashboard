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

namespace Surfnet\ServiceProviderDashboard\Application\Command\Entity;

use Surfnet\ServiceProviderDashboard\Application\Command\Command;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Symfony\Component\Validator\Constraints as Assert;

class CopyEntityCommand implements Command
{
    /**
     * @var string
     * @Assert\NotBlank
     * @Assert\Uuid
     */
    private $dashboardId;

    /**
     * @var string
     * @Assert\NotBlank
     * @Assert\Uuid
     */
    private $manageId;

    /**
     * @var Service
     * @Assert\NotNull
     */
    private $service;

    /**
     * @var string
     * @Assert\NotNull
     */
    private $environment;

    /**
     * @var SaveEntityCommand
     */
    private $saveEntityCommand;

    /**
     * @param SaveEntityCommand $saveEntityCommand
     * @param string $dashboardId
     * @param string $manageId
     * @param Service $service
     * @param string $environment
     */
    public function __construct(
        SaveEntityCommand $saveEntityCommand,
        $dashboardId,
        $manageId,
        Service $service,
        $environment
    ) {
        $this->saveEntityCommand = $saveEntityCommand;
        $this->dashboardId = $dashboardId;
        $this->manageId = $manageId;
        $this->service = $service;
        $this->environment = $environment;
    }

    /**
     * @return string
     */
    public function getDashboardId()
    {
        return $this->dashboardId;
    }

    /**
     * @return string
     */
    public function getManageId()
    {
        return $this->manageId;
    }

    /**
     * @return Service
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    public function getSaveEntityCommand()
    {
        return $this->saveEntityCommand;
    }
}
