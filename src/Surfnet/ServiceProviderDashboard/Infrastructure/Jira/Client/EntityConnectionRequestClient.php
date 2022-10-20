<?php

declare(strict_types=1);

/**
 * Copyright 2022 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\Jira\Client;

use Psr\Log\LoggerInterface;
use Surfnet\ServiceProviderDashboard\Domain\Repository\EntityConnectionRequestRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\Jira\Factory\JiraServiceFactory;

class EntityConnectionRequestClient implements EntityConnectionRequestRepository
{
    /**
     * @var JiraServiceFactory
     */
    private $factory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        JiraServiceFactory $jiraFactory,
        LoggerInterface $logger
    ) {
        $this->factory = $jiraFactory;
        $this->logger = $logger;
    }

    public function getOpenConnectionRequest(string $id): array
    {
        // TODO: connect to Jira and fetch open connection requests and built value objects from there
        return [
            'request-1' => [
                [
                    'idp' => 'Identity Provider 1',
                    'contact' => [
                        'firstName' => 'John',
                        'surName' => 'Doo',
                        'email' => 'john@doo.com'
                    ]
                ],
                [
                    'idp' => 'Identity Provider 2',
                    'contact' => [
                        'firstName' => 'Jack',
                        'surName' => 'Daniels',
                        'email' => 'jack@daniels.com'
                    ]
                ],
            ],
            'request-2' => [
                [
                    'idp' => 'Identity Provider 3',
                    'contact' => [
                        'firstName' => 'Barbara',
                        'surName' => 'Bush',
                        'email' => 'barbara@bush.com'
                    ]
                ],
            ]
        ];
    }
}
