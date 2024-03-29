<?php

/**
 * Copyright 2018 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Infrastructure\Jira\Factory;

use JiraRestApi\Issue\IssueService;
use Mockery as m;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Surfnet\ServiceProviderDashboard\Infrastructure\Jira\Factory\JiraServiceFactory;

class JiraServiceFactoryTest extends TestCase
{
    public function test_build_issue_service()
    {
        $hostname = 'https://jira.example.com/';
        $token = 'secret';
        $factory = new JiraServiceFactory(
            host: $hostname,
            personalAccessToken: $token,
            logger: m::mock(Logger::class));

        $issueService = $factory->buildIssueService();

        $this->assertInstanceOf(IssueService::class, $issueService);
    }
}
