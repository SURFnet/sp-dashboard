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

namespace Surfnet\ServiceProviderDashboard\Tests\Integration\Application\CommandHandler\Supplier;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Surfnet\ServiceProviderDashboard\Application\Command\Supplier\CreateSupplierCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\Supplier\CreateSupplierCommandHandler;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Supplier;
use Surfnet\ServiceProviderDashboard\Domain\Repository\SupplierRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Repository\SupplierRepository as DoctrineSupplierRepository;

class CreateSupplierCommandHandlerTest extends MockeryTestCase
{

    /** @var CreateSupplierCommandHandler */
    private $commandHandler;

    /** @var SupplierRepository|m\MockInterface */
    private $repository;

    public function setUp()
    {
        $this->repository = m::mock(DoctrineSupplierRepository::class);
        $this->commandHandler = new CreateSupplierCommandHandler($this->repository);
    }

    /**
     * @test
     * @group CommandHandler
     */
    public function it_can_process_a_create_supplier_command()
    {
        $entity = new Supplier();
        $entity->setName('Foobar');
        $entity->setTeamName('team-foobar');
        $entity->setGuid('30dd879c-ee2f-11db-8314-0800200c9a66');

        $command = new CreateSupplierCommand();
        $command->setName('Foobar');
        $command->setTeamName('team-foobar');
        $command->setGuid('30dd879c-ee2f-11db-8314-0800200c9a66');

        $this->repository->shouldReceive('save')->with(equalTo($entity))->once();
        $this->repository->shouldReceive('isUnique')->andReturn(true)->once();

        $this->commandHandler->handle($command);
    }

    /**
     * @test
     * @expectedException \Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException
     * @expectedExceptionMessage The Guid of the new Supplier should be unique.
     *                           This teamname is taken by: HZ with Guid: 30dd879c-ee2f-11db-8314-0800200c9a66
     * @group CommandHandler
     */
    public function it_rejects_non_unique_create_supplier_command()
    {
        $command = new CreateSupplierCommand();
        $command->setName('Foobar');
        $command->setTeamName('team-foobar');
        $command->setGuid('30dd879c-ee2f-11db-8314-0800200c9a66');

        $this->repository
            ->shouldReceive('isUnique')
            ->andThrow(
                InvalidArgumentException::class,
                'The Guid of the new Supplier should be unique. This teamname is taken by: HZ with Guid: 30dd879c-ee2f-11db-8314-0800200c9a66'
            )
            ->once();
        $this->commandHandler->handle($command);
    }
}
