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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Factory;

use GuzzleHttp\Client;
use Surfnet\ServiceProviderDashboard\Application\ViewObject\Apis\ApiConfig as Config;

class TeamsClientFactory
{
    public static function createClient(Config $configuration): Client
    {
        $arguments = [
            'base_uri' => $configuration->getConnection()->getHost(),
            'auth' => [
                $configuration->getConnection()->getUsername(),
                $configuration->getConnection()->getPassword(),
                'basic',
            ],
        ];

        return new Client($arguments);
    }
}
