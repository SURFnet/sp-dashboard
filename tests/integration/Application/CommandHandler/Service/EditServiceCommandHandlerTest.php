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

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Surfnet\ServiceProviderDashboard\Application\Command\Service\EditServiceCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\Service\EditServiceCommandHandler;
use Surfnet\ServiceProviderDashboard\Application\Exception\EntityNotFoundException;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Repository\ServiceRepository;

class EditServiceCommandHandlerTest extends MockeryTestCase
{

    /** @var EditServiceCommandHandler */
    private $commandHandler;

    /** @var ServiceRepository|m\MockInterface */
    private $repository;

    public function setUp()
    {
        $this->repository = m::mock(ServiceRepository::class);
        $this->commandHandler = new EditServiceCommandHandler($this->repository);
    }

    /**
     * @group CommandHandler
     */
    public function test_it_can_process_an_edit_service_command()
    {
        $command = new EditServiceCommand(
            '1',
            '30dd879c-ee2f-11db-8314-0800200c9a66',
            'Foobar',
            'team-foobar',
            false,
            false,
            true,
            'institution',
            'not-applicable',
            'no',
            'no',
            true,
            '22dd879c-ee2f-11db-8314-0800200c9a66',
            '123'
        );
        $command->setName('Foobar');
        $command->setTeamName('team-foobar');
        $command->setGuid('30dd879c-ee2f-11db-8314-0800200c9a66');
        $command->setPrivacyQuestionsEnabled(false);
        $command->setProductionEntitiesEnabled(false);

        $command->setPrivacyQuestionsAnswered('yes');
        $command->setServiceType('institution');
        $command->setIntakeStatus('not-applicable');
        $command->setSurfconextRepresentativeApproved('no');
        $command->setContractSigned('no');

        $mockEntity = m::mock(Service::class)->makePartial();
        $mockEntity->shouldReceive('getId')->andReturn(1);

        $this->repository
            ->shouldReceive('save')
            ->with(m::on(function ($arg) {
                $this->assertEquals(1, $arg->getId());
                $this->assertEquals('Foobar', $arg->getName());
                $this->assertEquals('team-foobar', $arg->getTeamName());
                $this->assertEquals('30dd879c-ee2f-11db-8314-0800200c9a66', $arg->getGuid());
                $this->assertEquals(false, $arg->isPrivacyQuestionsEnabled());
                $this->assertEquals(true, $arg->isOidcngEnabled());
                $this->assertEquals('no', $arg->getContractSigned());
                $this->assertEquals('no', $arg->getSurfconextRepresentativeApproved());
                $this->assertEquals('not-applicable', $arg->getIntakeStatus());
                $this->assertEquals('123', $arg->getInstitutionId());
                $this->assertEquals('22dd879c-ee2f-11db-8314-0800200c9a66', $arg->getInstitutionGuid());

                return true;
            }))
            ->once();
        $this->repository->shouldReceive('findById')->andReturn($mockEntity)->once();
        $this->repository->shouldReceive('isUnique')->andReturn(true)->once();

        $this->commandHandler->handle($command);
    }

    /**
     * Its highly unlikely to happen, but this tests the event that a service was removed while someone else is
     * editing it. An EntityNotFound exception is thrown in this case.
     *
     * @group CommandHandler
     */
    public function test_it_rejects_non_existing_service()
    {
        $this->expectExceptionMessage("The requested Service cannot be found");
        $this->expectException(EntityNotFoundException::class);
        $command = new EditServiceCommand(
            1,
            '30dd879c-ee2f-11db-8314-0800200c9a66',
            'Foobar',
            'team-foobar',
            false,
            true,
            true,
            'institution',
            'not-applicable',
            'no',
            'no',
            true,
            '22dd879c-ee2f-11db-8314-0800200c9a66',
            '123'
        );

        $this->repository->shouldReceive('findById')->andReturn(null)->once();

        $this->commandHandler->handle($command);
    }

    /**
     * @group CommandHandler
     */
    public function test_it_rejects_non_unique_edit_service_command()
    {
        $this->expectExceptionMessage("This teamname is taken by: HZ with Guid: 30dd879c-ee2f-11db-8314-0800200c9a66");
        $this->expectException(InvalidArgumentException::class);
        $command = new EditServiceCommand(
            1,
            '30dd879c-ee2f-11db-8314-0800200c9a66',
            'Foobar',
            'team-foobar',
            false,
            false,
            true,
            'institution',
            'not-applicable',
            'no',
            'no',
            true,
            '22dd879c-ee2f-11db-8314-0800200c9a66',
            '123'
        );

        $mockEntity = m::mock(Service::class)->makePartial();
        $mockEntity->shouldReceive('getId')->andReturn(1);

        $this->repository->shouldReceive('findById')->andReturn($mockEntity)->once();

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
