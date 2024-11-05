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

namespace Surfnet\ServiceProviderDashboard\Tests\Integration\Application\CommandHandler\Service;

use Hamcrest\Core\IsEqual;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Surfnet\ServiceProviderDashboard\Application\Command\Service\CreateServiceCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\Service\CreateServiceCommandHandler;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Application\Service\UuidValidator;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Repository\Invite\CreateRoleRepository;
use Surfnet\ServiceProviderDashboard\Domain\Repository\ServiceRepository;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\CreateRoleResponse;
use Surfnet\ServiceProviderDashboard\Infrastructure\Teams\Client\PublishEntityClient;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;

class CreateServiceCommandHandlerTest extends MockeryTestCase
{

    /** @var CreateServiceCommandHandler */
    private $commandHandler;

    /** @var ServiceRepository|m\MockInterface */
    private $repository;

    private CreateRoleRepository $inviteRepository;
    /**
     * @var PublishEntityClient
     */
    private $publishEntityClient;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function setUp(): void
    {
        $this->repository = m::mock(ServiceRepository::class);
        $this->publishEntityClient = m::mock(PublishEntityClient::class);
        $this->inviteRepository = m::mock(CreateRoleRepository::class);
        $this->translator = m::mock(TranslatorInterface::class);

        $this->commandHandler = new CreateServiceCommandHandler(
            $this->repository,
            $this->inviteRepository,
            $this->translator,
            'voor',
            'voegsel',
            'https://example.org',
        );
    }

    /**
     * @group CommandHandler
     */
    public function test_it_can_process_a_create_service_command()
    {
        $inviteUrn = 'urn:mace:surf.nl:test.surfaccess.nl:'.Uuid::v4()->toRfc4122().':required_role_name';

        $this->translator
            ->shouldReceive('trans');
        $this->publishEntityClient
            ->shouldReceive('createTeam')
            -> andReturn([]);
        $this->inviteRepository->shouldReceive('createRole')->withArgs(
            [
                'Foobar Organization Name EN',
                'Foobar Organization Name EN',
                '',
                'https://example.org',
                '920392e8-a1fc-4627-acb4-b1f215e11dcd'
            ]
        )->andReturns(new CreateRoleResponse(1234, 'foo', 'bar', 'baz', $inviteUrn));

        $service = new Service();
        $service->setName('Foobar');
        $service->setTeamName('team-foobar');
        $service->setGuid('30dd879c-ee2f-11db-8314-0800200c9a66');
        $service->setPrivacyQuestionsEnabled(true);
        $service->setProductionEntitiesEnabled(true);

        $service->setServiceType('institution');
        $service->setIntakeStatus('yes');
        $service->setSurfconextRepresentativeApproved('yes');
        $service->setContractSigned('yes');
        $service->setOrganizationNameEn('Organization Name EN');
        $service->setOrganizationNameNl('Organization Name NL');
        $service->registerInvite($inviteUrn, 1234);

        $command = new CreateServiceCommand('920392e8-a1fc-4627-acb4-b1f215e11dcd');
        $command->setName($service->getName());
        $command->setTeamName($service->getTeamName());
        $command->setTeamManagerEmail('tiffany@aching.do');
        $command->setGuid($service->getGuid());
        $command->setPrivacyQuestionsEnabled($service->isPrivacyQuestionsEnabled());
        $command->setProductionEntitiesEnabled($service->isProductionEntitiesEnabled());
        $command->setOrganizationNameEn($service->getOrganizationNameEn());
        $command->setOrganizationNameNl($service->getOrganizationNameNl());
        $command->setServiceType($service->getServiceType());
        $command->setIntakeStatus($service->getIntakeStatus());
        $command->setSurfconextRepresentativeApproved($service->getSurfconextRepresentativeApproved());
        $command->setContractSigned($service->getContractSigned());
        $command->setInstitutionId($service->getInstitutionId());

        $this->repository->shouldReceive('save')->with(IsEqual::equalTo($service))
            ->andReturnUsing(function ($service) {
                $service->setId(123);
                return $service;
            })->once();
        $this->repository->shouldReceive('isUnique')->andReturn(true)->once();
        $this->commandHandler->handle($command);

        $this->assertEquals(123, $command->getServiceId());
    }

    /**
     * @group CommandHandler
     */
    public function test_it_rejects_non_unique_create_service_command()
    {
        $this->translator
            ->shouldReceive('trans');
        $this->publishEntityClient
            ->shouldReceive('createTeam')
            -> andReturn([]);

        $this->inviteRepository->shouldReceive('createRole')->withArgs(
            [
                'Foobar Organization name',
                'Foobar Organization name',
                '',
                'https://example.org',
                '4'
            ]
        );

        $this->expectExceptionMessage("This teamname is taken by: HZ with Guid: 30dd879c-ee2f-11db-8314-0800200c9a66");
        $this->expectException(InvalidArgumentException::class);
        $command = new CreateServiceCommand('4b0e422d-d0d0-4b9e-a521-fdd1ee5d2bad');
        $command->setName('Foobar');
        $command->setTeamName('team-foobar');
        $command->setTeamManagerEmail('tiffany@aching.do');
        $command->setGuid('30dd879c-ee2f-11db-8314-0800200c9a66');
        $command->setProductionEntitiesEnabled(true);
        $command->setPrivacyQuestionsEnabled(false);

        $command->setServiceType('institution');
        $command->setIntakeStatus('yes');
        $command->setSurfconextRepresentativeApproved('yes');
        $command->setContractSigned('yes');
        $command->setOrganizationNameEn('Organization name');
        $command->setOrganizationNameNl('Organization name');

        $this->repository
            ->shouldReceive('isUnique')
            ->andThrow(
                InvalidArgumentException::class,
                'This teamname is taken by: HZ with Guid: 30dd879c-ee2f-11db-8314-0800200c9a66'
            )
            ->once();
        $this->commandHandler->handle($command);
    }
}
