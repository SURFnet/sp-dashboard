<?php

/**
 * Copyright 2021 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Application\ViewObject\Apis;

class ApiConfig
{
    /**
     * @var string
     */
    private $environment;

    /**
     * @var ApiConnection
     */
    private $connection;

    /**
     * @var PublicationStatus
     */
    private $publicationStatus;

    public function __construct(
        ApiConnection $connection,
        PublicationStatus $publicationStatus = null,
        string $environment = ''
    ) {
        $this->connection = $connection;
        $this->publicationStatus = $publicationStatus;
        $this->environment = $environment;
    }


    public function getEnvironment(): string
    {
        return $this->environment;
    }

    public function getConnection(): ApiConnection
    {
        return $this->connection;
    }

    public function getPublicationStatus(): PublicationStatus
    {
        return $this->publicationStatus;
    }
}
