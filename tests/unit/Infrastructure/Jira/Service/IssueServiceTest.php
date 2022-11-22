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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Infrastructure\Jira\Service;

use JiraRestApi\Issue\Issue;
use JiraRestApi\Issue\IssueField;
use JiraRestApi\Issue\IssueService as JiraIssueService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\Mock;
use Psr\Log\LoggerInterface;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Ticket;
use Surfnet\ServiceProviderDashboard\Infrastructure\Jira\Factory\IssueFieldFactory;
use Surfnet\ServiceProviderDashboard\Infrastructure\Jira\Factory\JiraServiceFactory;
use Surfnet\ServiceProviderDashboard\Infrastructure\Jira\Service\IssueService;

class IssueServiceTest extends MockeryTestCase
{
    /**
     * @var IssueService
     */
    private $service;

    /**
     * @var JiraServiceFactory|Mock
     */
    private $factory;
    /**
     * @var IssueFieldFactory|Mock
     */
    private $ticketFactory;

    /**
     * @var LoggerInterface|Mock
     */
    private $logger;

    /**
     * @var JiraIssueService|Mock
     */
    private $jiraIssueService;

    public function setUp(): void
    {
        $this->factory = m::mock(JiraServiceFactory::class);
        $this->ticketFactory = m::mock(IssueFieldFactory::class);
        $this->jiraIssueService = m::mock(JiraIssueService::class);
        $this->logger = m::mock(LoggerInterface::class);
        $this->service = new IssueService($this->factory, $this->ticketFactory, $this->logger);
    }

    public function test_create_issue()
    {
        $this->logger
            ->shouldReceive('info')
            ->once();

        $this->factory
            ->shouldReceive('buildIssueService')
            ->andReturn($this->jiraIssueService)
            ->once();

        $ticket = new Ticket(
            'Summary',
            'Description',
            'https://example.com',
            'arbitrary-summary-key',
            'arbitrary-description-key',
            'John Doe',
            'john@example.com',
            null
        );

        $issue = new Issue();
        $issueField = new IssueField();
        $issue->fields = $issueField;

        $this->jiraIssueService
            ->shouldReceive('create')
            ->with($issueField)
            ->andReturn($issue);

        $this->ticketFactory
            ->shouldReceive('fromTicket')
            ->with($ticket)
            ->andReturn($issueField);

        $jiraIssue = $this->service->createIssue($ticket);

        $this->assertInstanceOf(Issue::class, $jiraIssue);
    }
}
