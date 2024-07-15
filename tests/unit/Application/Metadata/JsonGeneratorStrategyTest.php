<?php

/**
 * Copyright 2019 SURFnet B.V.
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
use Mockery\Mock;
use Surfnet\ServiceProviderDashboard\Application\Dto\MetadataConversionDto;
use Surfnet\ServiceProviderDashboard\Application\Exception\JsonGeneratorStrategyNotFoundException;
use Surfnet\ServiceProviderDashboard\Application\Metadata\GeneratorInterface;
use Surfnet\ServiceProviderDashboard\Application\Metadata\JsonGeneratorStrategy;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Contact;
use Surfnet\ServiceProviderDashboard\Domain\Entity\EntityDiff;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;

class JsonGeneratorStrategyTest extends MockeryTestCase
{
    /**
     * @var GeneratorInterface&Mock
     */
    private $generator1;

    /**
     * @var GeneratorInterface&Mock
     */
    private $generator2;

    /**
     * @var GeneratorInterface&Mock
     */
    private $generator3;

    /**
     * @var JsonGeneratorStrategy
     */
    private $strategy;

    public function setUp(): void
    {
        $this->generator1 = m::mock(GeneratorInterface::class);
        $this->generator1->shouldReceive('generateForNewEntity');
        $this->generator1->shouldReceive('generateForExistingEntity');
        $this->generator2 = m::mock(GeneratorInterface::class);
        $this->generator2->shouldReceive('generateForNewEntity');
        $this->generator2->shouldReceive('generateForExistingEntity');
        $this->generator3 = m::mock(GeneratorInterface::class);
        $this->generator3->shouldReceive('generateForNewEntity');
        $this->generator3->shouldReceive('generateForExistingEntity');

        $this->strategy = new JsonGeneratorStrategy();
        $this->strategy->addStrategy('saml', $this->generator1);
        $this->strategy->addStrategy('oidcng', $this->generator3);
    }

    public function test_generate_for_new_entity()
    {
        $entity = m::mock(ManageEntity::class);

        $entity->shouldReceive('getProtocol->getProtocol')->andReturn('saml');
        $result = $this->strategy->generateForNewEntity($entity, 'prodaccepted', m::mock(Contact::class));
        $this->assertIsArray($result);

        $entity->shouldReceive('getProtocol->getProtocol')->andReturn('oidcng');
        $result = $this->strategy->generateForNewEntity($entity, 'prodaccepted', m::mock(Contact::class));
        $this->assertIsArray($result);
    }

    public function test_generate_for_existing_entity()
    {
        $entity = m::mock(ManageEntity::class);

        $entity->shouldReceive('getProtocol->getProtocol')->andReturn('saml');
        $diff = m::mock(EntityDiff::class);
        $result = $this->strategy->generateForExistingEntity($entity, $diff, 'prodaccepted');
        $this->assertIsArray($result);

        $entity->shouldReceive('getProtocol->getProtocol')->andReturn('oidcng');
        $result = $this->strategy->generateForExistingEntity($entity, $diff, 'prodaccepted');
        $this->assertIsArray($result);
    }

    public function test_add_invalid_strategy()
    {
        $entity = m::mock(ManageEntity::class);

        $entity->shouldReceive('getProtocol->getProtocol')->andReturn('saml30');

        $this->expectException(JsonGeneratorStrategyNotFoundException::class);
        $this->expectExceptionMessage('The requested JsonGenerator for protocol "saml30" is not registered');
        $diff = m::mock(EntityDiff::class);
        $this->strategy->generateForExistingEntity($entity, $diff, 'prodaccepted');
    }
}
