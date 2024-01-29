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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Application\Service;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Surfnet\ServiceProviderDashboard\Application\Service\ServiceService;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Repository\ServiceRepository;

class ServiceServiceTest extends MockeryTestCase
{
    private m\LegacyMockInterface|ServiceRepository|m\MockInterface $repository;

    private ServiceService $service;

    public function setUp(): void
    {
        $this->repository = m::mock(ServiceRepository::class);
        $this->service = new ServiceService($this->repository);
    }

    /**
     * @group Service
     */
    public function test_service_returns_service_options_sorted()
    {
        $this->repository->shouldReceive('findAll')
            ->andReturn([
                m::mock(Service::class)
                    ->shouldReceive('getId')
                    ->andReturn('c')->getMock()
                    ->shouldReceive('getName')
                    ->andReturn('C')->getMock()
                    ->shouldReceive('getTeamName')
                    ->andReturn('urn:example:1')->getMock(),
                m::mock(Service::class)
                    ->shouldReceive('getId')
                    ->andReturn('a')->getMock()
                    ->shouldReceive('getName')
                    ->andReturn('A')->getMock()
                    ->shouldReceive('getTeamName')
                    ->andReturn('urn:example:2')->getMock(),
                m::mock(Service::class)
                    ->shouldReceive('getId')
                    ->andReturn('b')->getMock()
                    ->shouldReceive('getName')
                    ->andReturn('B')->getMock()
                    ->shouldReceive('getTeamName')
                    ->andReturn('urn:example:3')->getMock(),
            ]);

        $this->assertEquals(
            [
                'a' => 'A [2]',
                'b' => 'B [3]',
                'c' => 'C [1]',
            ],
            $this->service->getServiceNamesById()
        );
    }
}
