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

class ConfigFactory
{
    /**
     * @param string $environment
     * @param array $config
     * @return Config
     */
    public static function fromConfig($config, $environment)
    {
        $connection = new Connection(
            $config['connection']['host'],
            $config['connection']['username'],
            $config['connection']['password']
        );

        $publicationStatus = new PublicationStatus(
            $config['publication_status']
        );

        return new Config($environment, $connection, $publicationStatus);
    }
}
