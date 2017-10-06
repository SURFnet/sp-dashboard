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

use Doctrine\ORM\EntityNotFoundException;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Surfnet\ServiceProviderDashboard\Application\Command\Supplier\EditSupplierCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\Supplier\EditSupplierCommandHandler;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Supplier;
use Surfnet\ServiceProviderDashboard\Domain\Repository\SupplierRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Repository\SupplierRepository as DoctrineSupplierRepository;

class EditSupplierCommandHandlerTest extends MockeryTestCase
{

    /** @var EditSupplierCommandHandler */
    private $commandHandler;

    /** @var SupplierRepository|m\MockInterface */
    private $repository;

    public function setUp()
    {
        $this->repository = m::mock(DoctrineSupplierRepository::class);
        $this->commandHandler = new EditSupplierCommandHandler($this->repository);
    }

    /**
     * @group CommandHandler
     */
    public function test_it_can_process_an_edit_supplier_command()
    {
        $command = new EditSupplierCommand('1', '30dd879c-ee2f-11db-8314-0800200c9a66', 'Foobar', 'team-foobar');
        $command->setName('Foobar');
        $command->setTeamName('team-foobar');
        $command->setGuid('30dd879c-ee2f-11db-8314-0800200c9a66');

        $mockEntity = m::mock(Supplier::class)->makePartial();
        $mockEntity->shouldReceive('getId')->andReturn(1);

        $this->repository
            ->shouldReceive('save')
            ->with(m::on(function ($arg) {
                $this->assertEquals(1, $arg->getId());
                $this->assertEquals('Foobar', $arg->getName());
                $this->assertEquals('team-foobar', $arg->getTeamName());
                $this->assertEquals('30dd879c-ee2f-11db-8314-0800200c9a66', $arg->getGuid());

                return true;
            }))
            ->once();
        $this->repository->shouldReceive('findById')->andReturn($mockEntity)->once();
        $this->repository->shouldReceive('isUnique')->andReturn(true)->once();

        $this->commandHandler->handle($command);
    }

    /**
     * Its highly unlikely to happen, but this tests the event that a supplier was removed while someone else is
     * editing it. An EntityNotFound exception is thrown in this case.
     *
     * @expectedException \Surfnet\ServiceProviderDashboard\Application\Exception\EntityNotFoundException
     * @expectedExceptionMessage The requested Supplier cannot be found
     * @group CommandHandler
     */
    public function test_it_rejects_non_existing_supplier()
    {
        $command = new EditSupplierCommand(1, '30dd879c-ee2f-11db-8314-0800200c9a66', 'Foobar', 'team-foobar');

        $this->repository->shouldReceive('findById')->andReturn(null)->once();

        $this->commandHandler->handle($command);
    }

    /**
     * @expectedException \Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException
     * @expectedExceptionMessage The Guid of the new Supplier should be unique.
     *                           This teamname is taken by: HZ with Guid: 30dd879c-ee2f-11db-8314-0800200c9a66
     * @group CommandHandler
     */
    public function test_it_rejects_non_unique_edit_supplier_command()
    {
        $command = new EditSupplierCommand(1, '30dd879c-ee2f-11db-8314-0800200c9a66', 'Foobar', 'team-foobar');

        $mockEntity = m::mock(Supplier::class)->makePartial();
        $mockEntity->shouldReceive('getId')->andReturn(1);

        $this->repository->shouldReceive('findById')->andReturn($mockEntity)->once();

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
