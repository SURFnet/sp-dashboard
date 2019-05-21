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

namespace Surfnet\ServiceProviderDashboard\Tests\Integration\Application\CommandHandler\Entity;

use JiraRestApi\Issue\Issue;
use JiraRestApi\JiraException;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\Mock;
use Monolog\Logger;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\RequestDeletePublishedEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\Entity\RequestDeletePublishedEntityCommandHandler;
use Surfnet\ServiceProviderDashboard\Application\Service\TicketService;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Contact;
use Surfnet\ServiceProviderDashboard\Infrastructure\Jira\Factory\JiraServiceFactory;
use Surfnet\ServiceProviderDashboard\Infrastructure\Jira\Repository\IssueRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\Jira\Service\IssueService;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\QueryClient;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Dto\ManageEntity;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class RequestDeletePublishedEntityCommandHandlerTest extends MockeryTestCase
{
    /**
     * @var TicketService|Mock
     */
    private $ticketService;

    /**
     * @var ManageQueryClient|Mock
     */
    private $queryClient;

    /**
     * @var FlashBagInterface|Mock
     */
    private $flashBag;

    /**
     * @var Logger|Mock
     */
    private $logger;

    /**
     * @var RequestDeletePublishedEntityCommandHandler
     */
    private $commandHandler;

    /**
     * @var EntityRemovalRequestRepository|Mock
     */
    private $entityRemovalRequestRepository;

    /**
     * @var JiraServiceFactory|Mock
     */
    private $jiraServiceFactory;

    /**
     * @var IssueRepository|Mock
     */
    private $issueRepository;

    public function setUp()
    {
        $this->queryClient = m::mock(QueryClient::class);
        $this->logger = m::mock(Logger::class);
        $this->entityRemovalRequestRepository = m::mock(EntityRemovalRequestRepository::class);
        $this->jiraServiceFactory = m::mock(JiraServiceFactory::class);
        $this->issueRepository = m::mock(IssueRepository::class);

        // As part of the integration test, the TicketService and IssueFieldFactory is not mocked but included in the test.
        $this->ticketService = new TicketService(
            $this->issueRepository,
            $this->entityRemovalRequestRepository
        );

        $this->flashBag = m::mock(FlashBagInterface::class);

        $this->commandHandler = new RequestDeletePublishedEntityCommandHandler(
            $this->queryClient,
            'arbitrary-issue-type',
            $this->ticketService,
            $this->flashBag,
            $this->logger
        );
    }

    public function test_handle()
    {
        $applicant = new Contact('john:doe', 'john@example.com', 'John Doe');
        $command = new RequestDeletePublishedEntityCommand(
            'd6f394b2-08b1-4882-8b32-81688c15c489',
            $applicant
        );

        $this->logger
            ->shouldReceive('info')
        ;

        $manageDto = ManageEntity::fromApiResponse([
            'id' => 'd6f394b2-08b1-4882-8b32-81688c15c489',
            'data' => [
                'entityid' => 'SP1',
                'metaDataFields' => [
                    'name:en' => 'SP1',
                    'contacts:0:contactType' => 'administrative',
                    'contacts:0:givenName' => 'Test',
                    'contacts:0:surName' => 'Test',
                    'contacts:0:emailAddress' => 'test@example.org',
                ],
            ],
        ]);

        $this->queryClient
            ->shouldReceive('findByManageId')
            ->with('d6f394b2-08b1-4882-8b32-81688c15c489')
            ->andReturn($manageDto);

        $issue = m::mock(Issue::class)->makePartial();
        $issue->shouldReceive('getKey')
            ->andReturn('CXT-999');

        $this->issueRepository
            ->shouldReceive('createIssueFrom')
            ->andReturn($issue);

        $this->entityRemovalRequestRepository
            ->shouldReceive('save');

        $this->commandHandler->handle($command);
    }

    public function test_jira_down()
    {
        $applicant = new Contact('john:doe', 'john@example.com', 'John Doe');
        $command = new RequestDeletePublishedEntityCommand(
            'd6f394b2-08b1-4882-8b32-81688c15c489',
            $applicant
        );

        $this->logger
            ->shouldReceive('info')
            ->shouldReceive('critical')
        ;

        $managetDto = ManageEntity::fromApiResponse([
            'id' => 'd6f394b2-08b1-4882-8b32-81688c15c489',
            'data' => [
                'entityid' => 'SP1',
                'metaDataFields' => [
                    'name:en' => 'SP1',
                    'contacts:0:contactType' => 'administrative',
                    'contacts:0:givenName' => 'Test',
                    'contacts:0:surName' => 'Test',
                    'contacts:0:emailAddress' => 'test@example.org',
                ],
            ],
        ]);

        $this->queryClient
            ->shouldReceive('findByManageId')
            ->with('d6f394b2-08b1-4882-8b32-81688c15c489')
            ->andReturn($managetDto);

        $issueService = m::mock(IssueService::class);

        $this->jiraServiceFactory
            ->shouldReceive('buildIssueService')
            ->andReturn($issueService);

        $issue = m::mock(Issue::class)->makePartial();
        $issue->key = 'CXT-999';

        $this->issueRepository
            ->shouldReceive('createIssueFrom')
            ->andThrow(JiraException::class);

        $this->flashBag
            ->shouldReceive('add')
            ->with('error', 'entity.delete.request.failed');

        $this->commandHandler->handle($command);
    }
}
