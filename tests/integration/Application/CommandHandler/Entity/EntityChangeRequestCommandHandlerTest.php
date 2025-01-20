<?php

/**
 * Copyright 2025 SURFnet B.V.
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

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\Mock;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\EntityChangeRequestCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\Entity\EntityChangeRequestCommandHandler;
use Surfnet\ServiceProviderDashboard\Application\Service\EntityServiceInterface;
use Surfnet\ServiceProviderDashboard\Application\Service\MailService;
use Surfnet\ServiceProviderDashboard\Application\Service\TicketService;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Contact;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\AllowedIdentityProviders;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\AttributeList;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Coin;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\ContactList;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Logo;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\MetaData;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Organization;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Protocol;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Repository\EntityChangeRequestRepository;
use Surfnet\ServiceProviderDashboard\Domain\Service\ContractualBaseService;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Issue;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\TypeOfServiceCollection;
use Symfony\Component\HttpFoundation\RequestStack;

class EntityChangeRequestCommandHandlerTest extends MockeryTestCase
{
    private EntityChangeRequestCommandHandler $commandHandler;

    private RequestStack|Mock $requestStack;

    private LoggerInterface|Mock $logger;

    private TicketService|Mock $ticketService;

    private MailService|Mock $mailService;

    private EntityServiceInterface|Mock $entityService;

    private EntityChangeRequestRepository|mock $entityChangeRequestRepository;
    public function setUp(): void
    {
        $this->entityChangeRequestRepository = m::mock(EntityChangeRequestRepository::class);
        $this->entityService = m::mock(EntityServiceInterface::class);
        $this->ticketService = m::mock(TicketService::class);
        $this->requestStack = m::mock(RequestStack::class);
        $this->logger = new NullLogger();

        $this->mailService = m::mock(MailService::class);

        $this->commandHandler = new EntityChangeRequestCommandHandler(
            $this->entityChangeRequestRepository,
            new ContractualBaseService(),
            $this->entityService,
            $this->ticketService,
            $this->requestStack,
            $this->mailService,
            $this->logger,
            'customIssueType'
        );

    }

    public static function contractualBaseProvider(){
        return [
            'Pristine equals enitity, no change' => [Constants::CONTRACTUAL_BASE_IX, []],
            'Pristine is null, update to correct value' => [null, ["metaDataFields.coin:contractual_base" => "IX"]],
            'Pristine is wrong, update to correct value' => [Constants::CONTRACTUAL_BASE_AO, ["metaDataFields.coin:contractual_base" => "IX"]],
        ];
    }

    /**
     * @dataProvider contractualBaseProvider
     */
    public function test_it_should_update_contractual_base_on_change_request(?string $pristineContractualBase, array $expectedDiff)
    {
        $manageEntity = $this->createEntity();

        $applicant = new Contact('john:doe', 'john@example.com', 'John Doe');

        $pristineEntity = unserialize(serialize($manageEntity));
        if($pristineContractualBase !== null) {
            $pristineEntity->getMetaData()->getCoin()->setContractualBase($pristineContractualBase);
        }

        $this->entityService->shouldReceive('getPristineManageEntityById')
            ->once()
            ->with($manageEntity->getId(), $manageEntity->getEnvironment())
            ->andReturn($pristineEntity);

        $ticket = new Issue('ISSUE-123', 'foo', 'bar');

        $this->ticketService->shouldReceive('createJiraTicket')
            ->once()
            ->andReturn($ticket);

        $this->entityChangeRequestRepository->shouldReceive('openChangeRequest')
            ->once()
            ->withArgs(function($entity, $pristineEntity) use ($expectedDiff){
                if($pristineEntity->diff($entity)->getDiff() !== $expectedDiff){
                    $this->fail(var_export($pristineEntity->diff($entity)->getDiff(), true) . 'is not equal to ' . var_export($expectedDiff, true));
                }
                return $pristineEntity->diff($entity)->getDiff() === $expectedDiff;
            })
            ->andReturn(['id' => 1]);
    
        $command = new EntityChangeRequestCommand($manageEntity, $applicant);
        $this->commandHandler->handle($command);
    }

    /**
     * @return ManageEntity
     */
    private function createEntity(): ManageEntity
    {
        $coin = new Coin(
            null,
            null,
            null,
            null,
            null,
            new TypeOfServiceCollection(),
            null,
            null,
            null,
            null
        );

        $manageEntity = new ManageEntity(
            1,
            new AttributeList(),
            new MetaData(
                'https://test.entity.id',
                'https://test.metadata.url',
                null,
                null,
                'Test Description',
                null,
                'Test Entity',
                null,
                new ContactList(),
                new Organization('Test org', "Test organisation", null, null, null, null),
                $coin,
                new Logo(null, null, null)
            ),
            new AllowedIdentityProviders([], true), new Protocol(Constants::TYPE_SAML),
            null,
            new Service()
        );
        $manageEntity->setId('f1e394b2-815b-4018-b780-898976322016');
        $manageEntity->setEnvironment('production');
        $manageEntity->setStatus('published');
        return $manageEntity;
    }

}
