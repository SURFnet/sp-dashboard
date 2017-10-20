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
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\EditEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Factory\EntityCommandFactory;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;

class EntityCommandFactoryTest extends MockeryTestCase
{

    /**
     * @var EntityCommandFactory
     */
    private $factory;

    public function setUp()
    {
        $this->factory = new EntityCommandFactory();
    }

    /**
     * @group Factory
     */
    public function test_it_can_build_a_command_from_an_entity()
    {
        /** @var Entity $entity */
        $entity = m::mock(Entity::class)->makePartial();

        $service = m::mock(Service::class);
        $service->shouldReceive('getName')->andReturn('Ibuildings');

        $entity->setService($service);
        $entity->setId('4e7d5872-3a58-4832-a0b4-d5df61d808f9');
        $entity->setTicketNumber('0766b312-36b2-47f7-a7da-4704b5581192');

        $command = $this->factory->build($entity);
        $this->assertInstanceOf(EditEntityCommand::class, $command);
        $this->assertEquals('4e7d5872-3a58-4832-a0b4-d5df61d808f9', $command->getId());
        $this->assertEquals('0766b312-36b2-47f7-a7da-4704b5581192', $command->getTicketNumber());
        $this->assertEquals('Ibuildings', $command->getService()->getName());
    }
}
