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
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\PublishEntityProductionCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\Entity\PublishEntityProductionCommandHandler;
use Surfnet\ServiceProviderDashboard\Application\Service\TicketService;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Contact;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Mailer\Mailer;
use Surfnet\ServiceProviderDashboard\Domain\Mailer\Message;
use Surfnet\ServiceProviderDashboard\Domain\Repository\EntityRepository;
use Surfnet\ServiceProviderDashboard\Domain\Repository\PublishEntityRepository;
use Surfnet\ServiceProviderDashboard\Domain\Repository\ServiceRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Factory\MailMessageFactory;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Authentication\Token\SamlToken;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Identity;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Exception\PublishMetadataException;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class PublishEntityProductionCommandHandlerTest extends MockeryTestCase
{

    /**
     * @var PublishEntityProductionCommandHandler
     */
    private $commandHandler;

    /**
     * @var EntityRepository|Mock
     */
    private $repository;

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
     * @var Mailer|Mock
     */
    private $mailer;

    /**
     * @var MailMessageFactory|Mock
     */
    private $mailFactory;

    /**
     * @var ServiceRepository|Mock
     */
    private $serviceRepository;

    public function setUp()
    {

        $this->repository = m::mock(EntityRepository::class);
        $this->serviceRepository = m::mock(ServiceRepository::class);
        $this->publishEntityClient = m::mock(PublishEntityRepository::class);
        $this->ticketService = m::mock(TicketService::class);
        $this->flashBag = m::mock(FlashBagInterface::class);
        $this->logger = m::mock(LoggerInterface::class);

        $this->mailer = m::mock(Mailer::class);
        $this->mailFactory = m::mock(MailMessageFactory::class);

        $this->commandHandler = new PublishEntityProductionCommandHandler(
            $this->repository,
            $this->serviceRepository,
            $this->publishEntityClient,
            $this->ticketService,
            $this->flashBag,
            $this->mailFactory,
            $this->mailer,
            $this->logger,
            'customIssueType'
        );

        parent::setUp();
    }

    public function test_it_can_publish()
    {
        $contact = new Contact('nameid', 'name@example.org', 'display name');
        $user = new Identity($contact);

        $token = new SamlToken([]);
        $token->setUser($user);

        $entity = m::mock(Entity::class);
        $entity
            ->shouldReceive('getId')
            ->andReturn('123');
        $entity
            ->shouldReceive('getNameEn')
            ->andReturn('Test Entity Name');
        $entity
            ->shouldReceive('getEntityId')
            ->andReturn('https://app.example.com/');
        $entity
            ->shouldReceive('getManageId')
            ->andReturn('the-manage-id');
        $entity
            ->shouldReceive('setStatus')
            ->with(Entity::STATE_PUBLISHED);
        $entity
            ->shouldReceive('getService->getConnectionStatus');

        $issue = m::mock(Issue::class)->makePartial();
        $issue->key = 'CXT-999';

        $this->ticketService
            ->shouldReceive('createIssueFrom')
            ->andReturn($issue);

        $this->ticketService
            ->shouldReceive('findByManageIdAndIssueType')
            ->andReturn(null);

        $this->repository
            ->shouldReceive('findById')
            ->with('d6f394b2-08b1-4882-8b32-81688c15c489')
            ->andReturn($entity);

        $this->repository
            ->shouldReceive('save')
            ->with($entity);

        $this->publishEntityClient
            ->shouldReceive('publish')
            ->once()
            ->with($entity)
            ->andReturn([
                'id' => 123,
            ]);

        $this->logger
            ->shouldReceive('info')
            ->times(4);


        $applicant = new Contact('john:doe', 'john@example.com', 'John Doe');
        $command = new PublishEntityProductionCommand('d6f394b2-08b1-4882-8b32-81688c15c489', $applicant);
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

        $entity = m::mock(Entity::class);
        $entity
            ->shouldReceive('getId')
            ->andReturn('123');
        $entity
            ->shouldReceive('getNameEn')
            ->andReturn('Test Entity Name');
        $entity
            ->shouldReceive('getEntityId')
            ->andReturn('https://app.example.com/');
        $entity
            ->shouldReceive('getManageId')
            ->andReturn('the-manage-id');
        $entity
            ->shouldReceive('setStatus')
            ->with(Entity::STATE_PUBLISHED);
        $entity
            ->shouldReceive('getService->getConnectionStatus');

        $issue = m::mock(Issue::class)->makePartial();
        $issue->key = 'CXT-999';

        $this->ticketService
            ->shouldReceive('findByManageIdAndIssueType')
            ->andReturn($issue);

        $this->ticketService
            ->shouldReceive('createIssueFrom')
            ->andReturn($issue);

        $this->repository
            ->shouldReceive('findById')
            ->with('d6f394b2-08b1-4882-8b32-81688c15c489')
            ->andReturn($entity);

        $this->repository
            ->shouldReceive('save')
            ->with($entity);

        $this->publishEntityClient
            ->shouldReceive('publish')
            ->once()
            ->with($entity)
            ->andReturn([
                'id' => 123,
            ]);

        $this->logger
            ->shouldReceive('info')
            ->times(4);


        $applicant = new Contact('john:doe', 'john@example.com', 'John Doe');
        $command = new PublishEntityProductionCommand('d6f394b2-08b1-4882-8b32-81688c15c489', $applicant);
        $this->commandHandler->handle($command);
    }

    public function test_failing_jira_ticket_creation()
    {
        $contact = new Contact('nameid', 'name@example.org', 'display name');
        $user = new Identity($contact);

        $token = new SamlToken([]);
        $token->setUser($user);

        $entity = m::mock(Entity::class);
        $entity
            ->shouldReceive('getId')
            ->andReturn('123');
        $entity
            ->shouldReceive('getNameEn')
            ->andReturn('Test Entity Name');
        $entity
            ->shouldReceive('getEntityId')
            ->andReturn('https://app.example.com/');
        $entity
            ->shouldReceive('getManageId')
            ->andReturn('the-manage-id');

        $this->repository
            ->shouldReceive('findById')
            ->with('d6f394b2-08b1-4882-8b32-81688c15c489')
            ->andReturn($entity);

        $this->ticketService
            ->shouldReceive('createIssueFrom')
            ->andThrow(JiraException::class);

        $message = m::mock(Message::class);
        $this->mailFactory
            ->shouldReceive('buildJiraIssueFailedMessage')
            ->andReturn($message);

        $this->mailer
            ->shouldReceive('send')
            ->with($message);

        $this->logger
            ->shouldReceive('info')
            ->once();

        $this->logger
            ->shouldReceive('critical')
            ->once();

        $this->flashBag
            ->shouldReceive('add')
            ->with('error', 'entity.edit.error.publish');

        $applicant = new Contact('john:doe', 'john@example.com', 'John Doe');

        $command = new PublishEntityProductionCommand('d6f394b2-08b1-4882-8b32-81688c15c489', $applicant);
        $this->commandHandler->handle($command);
    }

    public function test_it_handles_failing_publish_and_cleans_up_ticket()
    {
        $entity = m::mock(Entity::class);
        $entity
            ->shouldReceive('getId')
            ->andReturn('123');
        $entity
            ->shouldReceive('getNameEn')
            ->andReturn('Test Entity Name');
        $entity
            ->shouldReceive('setStatus')
            ->with(Entity::STATE_PUBLISHED);
        $entity
            ->shouldReceive('getEntityId')
            ->andReturn('https://app.example.com/');
        $entity
            ->shouldReceive('getManageId')
            ->andReturn('the-manage-id');

        $issue = m::mock(Issue::class)->makePartial();
        $issue->key = 'CXT-999';

        $this->ticketService
            ->shouldReceive('findByManageIdAndIssueType')
            ->andReturn(null);

        $this->ticketService
            ->shouldReceive('createIssueFrom')
            ->andReturn($issue);

        $this->repository
            ->shouldReceive('findById')
            ->with('d6f394b2-08b1-4882-8b32-81688c15c489')
            ->andReturn($entity);

        $this->repository
            ->shouldReceive('save')
            ->with($entity);

        $this->logger
            ->shouldReceive('info')
            ->times(4);

        $this->logger
            ->shouldReceive('error')
            ->times(1);

        $this->publishEntityClient
            ->shouldReceive('publish')
            ->once()
            ->with($entity)
            ->andThrow(PublishMetadataException::class);

        $this->flashBag
            ->shouldReceive('add')
            ->with('error', 'entity.edit.error.publish');

        $this->ticketService
            ->shouldReceive('delete');

        $applicant = new Contact('john:doe', 'john@example.com', 'John Doe');
        $command = new PublishEntityProductionCommand('d6f394b2-08b1-4882-8b32-81688c15c489', $applicant);
        $this->commandHandler->handle($command);
    }
}
