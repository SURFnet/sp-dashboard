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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\Manage;

use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\HttpClient;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\HttpClientInterface;

final class ManageClient extends HttpClient implements HttpClientInterface
{
    /**
     * @param ClientInterface $httpClient
     * @param LoggerInterface $logger
     * @param string $mode The mode is used mainly for logging purposes, stating which environment was targeted.
     */
    public function __construct(ClientInterface $httpClient, LoggerInterface $logger, string $mode = self::MODE_TEST)
    {
        parent::__construct($httpClient, $logger, 'manage', $mode);
    }
}
