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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Exception;

use Surfnet\SamlBundle\Security\Exception\RuntimeException;

class UnknownServiceException extends RuntimeException
{
    public function __construct(private readonly array $teamNames, $message = "")
    {
        parent::__construct($message, 400, null);
    }

    /**
     * Get all team names the user has access to
     */
    public function getTeamNames(): array
    {
        return $this->teamNames;
    }
}
