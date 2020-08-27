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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Infrastructure\DashboardBundle\Form\Entity;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Exception\InvalidEnvironmentException;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Entity\OidcngResourceServerOptionsFactory;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\QueryClient;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;

class OidcngResourceServerOptionsFactoryTest extends MockeryTestCase
{
    /**
     * @var QueryClient
     */
    private $testEntityRepository;

    /**
     * @var QueryClient
     */
    private $productionEntityRepository;

    private $factory;

    public function setUp()
    {
        $this->testEntityRepository = m::mock(QueryClient::class);
        $this->productionEntityRepository = m::mock(QueryClient::class);

        $this->factory = new OidcngResourceServerOptionsFactory(
            $this->testEntityRepository,
            $this->productionEntityRepository,
            'prodaccepted',
            'prodaccepted'
        );
    }

    public function test_build_creates_usable_choices_for_test()
    {
        $expectedChoices = ['Entity 1 (www.example.com)' => 'www.example.com'];

        $entity = m::mock(ManageEntity::class);
        $entity
            ->shouldReceive('getMetaData->getNameEn')
            ->andReturn('Entity 1');
        $entity
            ->shouldReceive('getOidcClient->getClientId')
            ->andReturn('www.example.com');

        $this->testEntityRepository
            ->shouldReceive('findOidcngResourceServersByTeamName')
            ->with('team-1', 'prodaccepted')
            ->andReturn([$entity]);

        $choices = $this->factory->build('team-1', 'test');

        $this->assertEquals($expectedChoices, $choices);
    }

    public function test_build_creates_usable_choices_for_production()
    {
        $expectedChoices = ['Entity 1 (www.example.com)' => 'www.example.com'];

        $entity = m::mock(ManageEntity::class);
        $entity
            ->shouldReceive('getMetaData->getNameEn')
            ->andReturn('Entity 1');
        $entity
            ->shouldReceive('getOidcClient->getClientId')
            ->andReturn('www.example.com');

        $this->productionEntityRepository
            ->shouldReceive('findOidcngResourceServersByTeamName')
            ->with('team-1', 'prodaccepted')
            ->andReturn([$entity]);

        $choices = $this->factory->build('team-1', 'production');

        $this->assertEquals($expectedChoices, $choices);
    }

    public function test_does_not_process_unknown_environment()
    {
        $this->expectException(InvalidEnvironmentException::class);
        $this->expectExceptionMessage('Environment "acceptance" is not supported');
        $this->factory->build('team-1', 'acceptance');
    }
}
