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

class LoadMetadataCommand implements Command
{
    /**
     * @var EditServiceCommand
     */
    private $editServiceCommand;

    public function __construct(EditServiceCommand $command)
    {
        $this->editServiceCommand = $command;
    }

    public function getServiceId()
    {
        return $this->editServiceCommand->getId();
    }

    public function isUrlSet()
    {
        if (!empty($this->editServiceCommand->getImportUrl())) {
            return true;
        }
        return false;
    }

    public function isXmlSet()
    {
        if (!empty($this->editServiceCommand->getMetadataXml())) {
            return true;
        }
        return false;
    }

    public function getImportUrl()
    {
        return $this->editServiceCommand->getImportUrl();
    }

    public function getMetadataXml()
    {
        return $this->editServiceCommand->getMetadataXml();
    }
}
