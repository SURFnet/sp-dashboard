<?php

/**
 * Copyright 2018 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Application\Command;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\DeleteCommandFactory;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\DeleteDraftEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\DeletePublishedProductionEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\DeletePublishedTestEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\RequestDeletePublishedEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\ViewObject\Entity;

class DeleteCommandFactoryTest extends MockeryTestCase
{
    private $factory;

    public function setUp()
    {
        $this->factory = new DeleteCommandFactory();
    }

    /**
     * @dataProvider testEntities
     * @param Entity $entity
     * @param mixed $expectedCommand
     * @param string $testDescription
     */
    public function test_from(Entity $entity, $expectedCommand, $testDescription)
    {
        $command = $this->factory->from($entity);
        $this->assertInstanceOf($expectedCommand, $command, $testDescription);
    }

    /**
     * @expectedException \Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException
     * @expectedExceptionMessage This entity state/environment combination is not supported for deleting
     */
    public function test_invalid_entity_results_in_exception()
    {
        $entity = m::mock(Entity::class);
        $entity->shouldReceive('getState')->andReturn('Wisconsin');
        $entity->shouldReceive('getEnvironment')->andReturn('Desert');

        $this->factory->from($entity);
    }

    public function testEntities()
    {
        return [
            [
                $this->buildEntity('test', 'draft'),
                DeleteDraftEntityCommand::class,
                'Test draft should result in DeleteDraftEntityCommand',
            ],
            [
                $this->buildEntity('test', 'published'),
                DeletePublishedTestEntityCommand::class,
                'Test published should result in DeletePublishedTestEntityCommand',
            ],
            [
                $this->buildEntity('production', 'draft'),
                DeleteDraftEntityCommand::class,
                'Production draft should result in DeleteDraftEntityCommand',
            ],
            [
                $this->buildEntity('production', 'requested'),
                DeletePublishedProductionEntityCommand::class,
                'Production request should result in DeletePublishedProductionEntityCommand',
            ],
            [
                $this->buildEntity('production', 'published'),
                RequestDeletePublishedEntityCommand::class,
                'Production published should result in RequestDeletePublishedEntityCommand',
            ],
        ];
    }

    private function buildEntity($environment, $status)
    {
        $entity = m::mock(Entity::class);
        $entity
            ->shouldReceive('getId')
            ->andReturn(1);

        $entity
            ->shouldReceive('getState')
            ->andReturn($status);
        $entity
            ->shouldReceive('getEnvironment')
            ->andReturn($environment);

        return $entity;
    }
}
