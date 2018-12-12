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
     * @var SaveSamlEntityCommand
     */
    private $saveEntityCommand;

    private $requestData;

    public function __construct(SaveSamlEntityCommand $command, array $requestData)
    {
        $this->saveEntityCommand = $command;
        $this->requestData = $requestData;
    }

    /**
     * @return SaveSamlEntityCommand
     */
    public function getSaveEntityCommand()
    {
        return $this->saveEntityCommand;
    }

    public function getDashboardId()
    {
        return $this->saveEntityCommand->getId();
    }

    public function isUrlSet()
    {
        return !empty($this->requestData['metadata']['importUrl']);
    }

    public function isXmlSet()
    {
        return !empty($this->requestData['metadata']['pastedMetadata']);
    }

    public function getImportUrl()
    {
        return $this->requestData['metadata']['importUrl'];
    }

    public function getPastedMetadata()
    {
        return $this->requestData['metadata']['pastedMetadata'];
    }

    public function setNameIdFormat($nameIdFormat)
    {
        $this->saveEntityCommand->setNameIdFormat($nameIdFormat);
    }
}
