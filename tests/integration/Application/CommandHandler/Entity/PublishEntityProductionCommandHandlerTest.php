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

namespace Surfnet\ServiceProviderDashboard\Tests\Integration\Application\CommandHandler\Entity;

use JiraRestApi\Issue\Issue;
use JiraRestApi\JiraException;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\Mock;
use Psr\Log\LoggerInterface;
use Surfnet\SamlBundle\Security\Authentication\Token\SamlToken;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\PublishEntityProductionCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\Entity\PublishEntityProductionCommandHandler;
use Surfnet\ServiceProviderDashboard\Application\Service\EntityServiceInterface;
use Surfnet\ServiceProviderDashboard\Application\Service\MailService;
use Surfnet\ServiceProviderDashboard\Application\Service\TicketService;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Contact;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\Repository\PublishEntityRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Identity;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class PublishEntityProductionCommandHandlerTest extends MockeryTestCase
{

    /**
     * @var PublishEntityProductionCommandHandler
     */
    private $commandHandler;

    /**
     * @var PublishEntityRepository|Mock
     */
    private $publishEntityClient;

    /**
     * @var FlashBagInterface|Mock
     */
    private $flashBag;

    /**
     * @var LoggerInterface|Mock
     */
    private $logger;

    /**
     * @var TicketService|Mock
     */
    private $ticketService;

    /**
     * @var MailService|Mock
     */
    private $mailService;

    /**
     * @var EntityServiceInterface|Mock
     */
    private $entityService;

    public function setUp(): void
    {
        $this->publishEntityClient = m::mock(PublishEntityRepository::class);
        $this->entityService = m::mock(EntityServiceInterface::class);
        $this->ticketService = m::mock(TicketService::class);
        $this->flashBag = m::mock(FlashBagInterface::class);
        $this->logger = m::mock(LoggerInterface::class);

        $this->mailService = m::mock(MailService::class);

        $this->commandHandler = new PublishEntityProductionCommandHandler(
            $this->publishEntityClient,
            $this->entityService,
            $this->ticketService,
            $this->flashBag,
            $this->mailService,
            $this->logger,
            'customIssueType'
        );

        parent::setUp();
    }

    public function test_it_can_publish()
    {
        $contact = new Contact('nameid', 'name@example.org', 'display name');
        $user = new Identity($contact);

        $token = new SamlToken($user, 'tst', ['ROLE_ADMIN'], []);

        $manageEntity = m::mock(ManageEntity::class);
        $manageEntity
            ->shouldReceive('getMetaData->getNameEn')
            ->andReturn('Test Entity Name');
        $manageEntity->shouldReceive('isManageEntity')->andReturnTrue();
        $manageEntity->shouldReceive('getEnvironment')->andReturn('production');

        $this->publishEntityClient
            ->shouldReceive('publish')
            ->once()
            ->with($manageEntity, $manageEntity)
            ->andReturn([
                'id' => '123',
            ]);

        $manageEntity
            ->shouldReceive('getMetaData->getEntityId')
            ->andReturn('https://app.example.com/');

        $manageEntity
            ->shouldReceive('getId')
            ->andReturn('123');

        $manageEntity
            ->shouldReceive('setStatus')
            ->with(Constants::STATE_PUBLISHED);
        $manageEntity
            ->shouldReceive('setId')
            ->with('123');

        $manageEntity
            ->shouldReceive('getService->getConnectionStatus');

        $issue = m::mock(Issue::class)->makePartial();
        $issue->shouldReceive('getKey')
            ->andReturn('CXT-999');

        $this->ticketService
            ->shouldReceive('createJiraTicket');

        $this->logger
            ->shouldReceive('info')
            ->times(2);
        $this->entityService->shouldReceive('getManageEntityById')->andReturn($manageEntity);

        $applicant = new Contact('john:doe', 'john@example.com', 'John Doe');
        $command = new PublishEntityProductionCommand($manageEntity, $applicant);
        $this->commandHandler->handle($command);
    }

    /**
     * Republishing an entity should not result in the creation of a new Jira ticket. The existing ticket should
     * be retrieved and used in the further logging.
     */
    public function test_it_can_republish()
    {
        $contact = new Contact('nameid', 'name@example.org', 'display name');
        $user = new Identity($contact);

        $token = new SamlToken([]);
        $token->setUser($user);

        $manageEntity = m::mock(ManageEntity::class);
        $manageEntity
            ->shouldReceive('getMetaData->getNameEn')
            ->andReturn('Test Entity Name');

        $this->publishEntityClient
            ->shouldReceive('publish')
            ->once()
            ->with($manageEntity, $manageEntity)
            ->andReturn([
                'id' => '123',
            ]);

        $manageEntity
            ->shouldReceive('getMetaData->getEntityId')
            ->andReturn('https://app.example.com/');

        $manageEntity
            ->shouldReceive('getId')
            ->andReturn('123');
        $manageEntity->shouldReceive('isManageEntity')->andReturnTrue();
        $manageEntity->shouldReceive('getEnvironment')->andReturn('production');

        $manageEntity
            ->shouldReceive('setStatus')
            ->with(Constants::STATE_PUBLISHED);
        $manageEntity
            ->shouldReceive('setId')
            ->with('123');

        $manageEntity
            ->shouldReceive('getService->getConnectionStatus');

        $issue = m::mock(Issue::class)->makePartial();
        $issue->shouldReceive('getKey')
            ->andReturn('CXT-999');

        $this->ticketService
            ->shouldReceive('createJiraTicket');

        $this->logger
            ->shouldReceive('info')
            ->times(2);
        $this->entityService->shouldReceive('getManageEntityById')->andReturn($manageEntity);


        $applicant = new Contact('john:doe', 'john@example.com', 'John Doe');
        $command = new PublishEntityProductionCommand($manageEntity, $applicant);
        $this->commandHandler->handle($command);
    }

    public function test_failing_jira_ticket_creation()
    {
        $contact = new Contact('nameid', 'name@example.org', 'display name');
        $user = new Identity($contact);

        $token = new SamlToken([]);
        $token->setUser($user);

        $manageEntity = m::mock(ManageEntity::class);
        $manageEntity
            ->shouldReceive('getMetaData->getNameEn')
            ->andReturn('Test Entity Name');

        $manageEntity
            ->shouldReceive('getMetaData->getEntityId')
            ->andReturn('https://app.example.com/');

        $manageEntity
            ->shouldReceive('getId')
            ->andReturn('123');
        $manageEntity->shouldReceive('isManageEntity')->andReturnTrue();
        $manageEntity->shouldReceive('getEnvironment')->andReturn('production');

        $manageEntity
            ->shouldReceive('setStatus')
            ->with(Constants::STATE_PUBLISHED);
        $manageEntity
            ->shouldReceive('setId')
            ->with('123');

        $manageEntity
            ->shouldReceive('getService->getConnectionStatus');

        $this->ticketService
            ->shouldReceive('createIssueFrom')
            ->andThrow(JiraException::class);

        $this->mailService
            ->shouldReceive('sendErrorReport');

        $this->logger
            ->shouldReceive('info')
            ->once();

        $this->logger
            ->shouldReceive('critical')
            ->once();

        $this->flashBag
            ->shouldReceive('add')
            ->with('error', 'entity.edit.error.publish');
        $this->entityService->shouldReceive('getManageEntityById')->andReturn($manageEntity);

        $applicant = new Contact('john:doe', 'john@example.com', 'John Doe');

        $command = new PublishEntityProductionCommand($manageEntity, $applicant);
        $this->commandHandler->handle($command);
    }
}
