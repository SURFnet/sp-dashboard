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
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Repository\ServiceRepository;

class CreateServiceCommandHandlerTest extends MockeryTestCase
{

    /** @var CreateServiceCommandHandler */
    private $commandHandler;

    /** @var ServiceRepository|m\MockInterface */
    private $repository;

    public function setUp()
    {
        $this->repository = m::mock(ServiceRepository::class);
        $this->commandHandler = new CreateServiceCommandHandler($this->repository);
    }

    /**
     * @group CommandHandler
     */
    public function test_it_can_process_a_create_service_command()
    {
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

        $command = new CreateServiceCommand();
        $command->setName($service->getName());
        $command->setTeamName($service->getTeamName());
        $command->setGuid($service->getGuid());
        $command->setPrivacyQuestionsEnabled($service->isPrivacyQuestionsEnabled());
        $command->setProductionEntitiesEnabled($service->isProductionEntitiesEnabled());

        $command->setServiceType($service->getServiceType());
        $command->setIntakeStatus($service->getIntakeStatus());
        $command->setSurfconextRepresentativeApproved($service->getSurfconextRepresentativeApproved());
        $command->setContractSigned($service->getContractSigned());
        $command->setInstitutionId($service->getInstitutionId());

        $this->repository->shouldReceive('save')->with(IsEqual::equalTo($service))->once();
        $this->repository->shouldReceive('isUnique')->andReturn(true)->once();

        $this->commandHandler->handle($command);
    }

    /**
     * @group CommandHandler
     */
    public function test_it_rejects_non_unique_create_service_command()
    {
        $this->expectExceptionMessage("This teamname is taken by: HZ with Guid: 30dd879c-ee2f-11db-8314-0800200c9a66");
        $this->expectException(InvalidArgumentException::class);
        $command = new CreateServiceCommand();
        $command->setName('Foobar');
        $command->setTeamName('team-foobar');
        $command->setGuid('30dd879c-ee2f-11db-8314-0800200c9a66');
        $command->setProductionEntitiesEnabled(true);
        $command->setPrivacyQuestionsEnabled(false);

        $command->setServiceType('institution');
        $command->setIntakeStatus('yes');
        $command->setSurfconextRepresentativeApproved('yes');
        $command->setContractSigned('yes');

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
