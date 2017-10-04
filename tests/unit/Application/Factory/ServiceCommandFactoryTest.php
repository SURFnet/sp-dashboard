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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Application\Factory;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Surfnet\ServiceProviderDashboard\Application\Command\Service\EditServiceCommand;
use Surfnet\ServiceProviderDashboard\Application\Factory\ServiceCommandFactory;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Supplier;

class ServiceCommandFactoryTest extends MockeryTestCase
{

    /**
     * @var ServiceCommandFactory
     */
    private $factory;

    public function setUp()
    {
        $this->factory = new ServiceCommandFactory();
    }

    /**
     * @test
     * @group Factory
     */
    public function it_can_build_a_command_from_an_entity()
    {
        /** @var Service $entity */
        $entity = m::mock(Service::class)->makePartial();

        $supplier = m::mock(Supplier::class);
        $supplier->shouldReceive('getName')->andReturn('Ibuildings');

        $entity->setSupplier($supplier);
        $entity->setId('4e7d5872-3a58-4832-a0b4-d5df61d808f9');
        $entity->setTicketNumber('0766b312-36b2-47f7-a7da-4704b5581192');

        $command = $this->factory->build($entity);
        $this->assertInstanceOf(EditServiceCommand::class, $command);
        $this->assertEquals('4e7d5872-3a58-4832-a0b4-d5df61d808f9', $command->getId());
        $this->assertEquals('0766b312-36b2-47f7-a7da-4704b5581192', $command->getTicketNumber());
        $this->assertEquals('Ibuildings', $command->getSupplier()->getName());
    }
}
