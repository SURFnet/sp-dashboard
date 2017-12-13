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

class LoadMetadataCommand implements Command
{
    /**
     * @var string
     */
    private $dashboardId;

    /**
     * @var string
     */
    private $importUrl;

    /**
     * @var string
     */
    private $pastedMetadata;

    public function __construct($dashboardId, $importUrl, $pastedMetadata)
    {
        $this->dashboardId = $dashboardId;
        $this->importUrl = $importUrl;
        $this->pastedMetadata = $pastedMetadata;
    }

    public static function fromEditCommand(SaveEntityCommand $command)
    {
        return new self(
            $command->getId(),
            $command->getImportUrl(),
            $command->getPastedMetadata()
        );
    }

    public function getDashboardId()
    {
        return $this->dashboardId;
    }

    public function isUrlSet()
    {
        return (bool) $this->getImportUrl();
    }

    public function isXmlSet()
    {
        return (bool) $this->getPastedMetadata();
    }

    public function getImportUrl()
    {
        return $this->importUrl;
    }

    public function getPastedMetadata()
    {
        return $this->pastedMetadata;
    }
}
