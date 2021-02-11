<?php

/**
 * Copyright 2019 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Application\ViewObject\Manage;

class Config
{
    /**
     * @var string
     */
    private $environment;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var PublicationStatus
     */
    private $publicationStatus;

    /**
     * @param string $environment
     * @param Connection $connection
     * @param PublicationStatus $publicationStatus
     */
    public function __construct(
        $environment,
        Connection $connection,
        PublicationStatus $publicationStatus
    ) {
        $this->environment = $environment;
        $this->connection = $connection;
        $this->publicationStatus = $publicationStatus;
    }

    public function getEnvironment()
    {
        return (string)$this->environment;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function getPublicationStatus()
    {
        return $this->publicationStatus;
    }
}
