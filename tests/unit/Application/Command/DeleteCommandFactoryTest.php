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
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\DeletePublishedProductionEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\DeletePublishedTestEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\RequestDeletePublishedEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Dto\EntityDto;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Contact;

class DeleteCommandFactoryTest extends MockeryTestCase
{
    /**
     * @var DeleteCommandFactory
     */
    private $factory;

    public function setUp(): void
    {
        $this->factory = new DeleteCommandFactory();
    }

    /**
     * @dataProvider provideEntities
     * @param EntityDto $entity
     * @param mixed $expectedCommand
     * @param string $testDescription
     * @throws InvalidArgumentException
     */
    public function test_from(EntityDto $entity, $expectedCommand, $testDescription)
    {
        $command = $this->factory->from($entity);
        $this->assertInstanceOf($expectedCommand, $command, $testDescription);
    }

    public function test_invalid_entity_results_in_exception()
    {
        $this->expectExceptionMessage("This entity state/environment combination is not supported for deleting");
        $this->expectException(\Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException::class);
        $entity = m::mock(EntityDto::class);
        $entity->shouldReceive('getState')->andReturn('Wisconsin');
        $entity->shouldReceive('getEnvironment')->andReturn('Desert');
        $entity->shouldReceive('getContact')->andReturn(m::mock(Contact::class));

        $this->factory->from($entity);
    }

    public function provideEntities()
    {
        return [
            [
                $this->buildEntity('test', 'published'),
                DeletePublishedTestEntityCommand::class,
                'Test published should result in DeletePublishedTestEntityCommand',
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
        $entity = m::mock(EntityDto::class);
        $entity
            ->shouldReceive('getId')
            ->andReturn(1);
        $entity
            ->shouldReceive('getState')
            ->andReturn($status);
        $entity
            ->shouldReceive('getEnvironment')
            ->andReturn($environment);
        $entity
            ->shouldReceive('getContact')
            ->andReturn(m::mock(Contact::class));
        $entity
            ->shouldReceive('getProtocol')
            ->andReturn(Constants::TYPE_SAML);
        return $entity;
    }
}
