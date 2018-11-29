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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Application\Service;

use Mockery as m;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Application\Service\TicketService;
use Surfnet\ServiceProviderDashboard\Domain\Entity\EntityRemovalRequest;
use Surfnet\ServiceProviderDashboard\Domain\Repository\EntityRemovalRequestRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\Jira\Factory\IssueFieldFactory;
use Surfnet\ServiceProviderDashboard\Infrastructure\Jira\Factory\JiraServiceFactory;

class TicketServiceTest extends MockeryTestCase
{
    /** @var EntityRemovalRequestRepository|m\MockInterface */
    private $repository;

    /**
     * @var TicketService
     */
    private $service;

    /**
     * @var JiraServiceFactory
     */
    private $issueFieldFactory;

    /**
     * @var IssueFieldFactory
     */
    private $jiraServiceFactory;

    public function setUp()
    {
        $this->repository = m::mock(EntityRemovalRequestRepository::class);
        $this->jiraServiceFactory = m::mock(JiraServiceFactory::class);
        $this->issueFieldFactory = m::mock(IssueFieldFactory::class);
        $this->service = new TicketService($this->jiraServiceFactory, $this->issueFieldFactory, $this->repository);
    }

    public function test_it_can_save_a_ticket_reference()
    {
        $this->repository
            ->shouldReceive('save')
            ->with(Mockery::on(function (EntityRemovalRequest $entity) {
                $expectedManageId = 'ca65c4be-f3d7-11e8-8eb2-f2801f1b9fd1';
                $expectedJiraTicketKey = 'CXT-123456';
                return $entity->getManageId() == $expectedManageId && $entity->getTicketKey() == $expectedJiraTicketKey;
            }));

        $this->service->storeTicket('CXT-123456', 'ca65c4be-f3d7-11e8-8eb2-f2801f1b9fd1');
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage jiraIssueKey id must be a string
     */
    public function test_it_rejects_invalid_input_on_saving_a_ticket_reference_invalid_jira_key()
    {
        $this->service->storeTicket(true, 'ca65c4be-f3d7-11e8-8eb2-f2801f1b9fd1');
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage manageId id must be a string
     */
    public function test_it_rejects_invalid_input_on_saving_a_ticket_reference_invalid_manage_id()
    {
        $this->service->storeTicket('CXT-123456', null);
    }
}
