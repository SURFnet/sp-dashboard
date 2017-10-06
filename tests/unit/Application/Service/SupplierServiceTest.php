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
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Repository\SupplierRepository;
use Surfnet\ServiceProviderDashboard\Application\Service\SupplierService;

class SupplierServiceTest extends MockeryTestCase
{
    /** @var ServiceRepository|m\MockInterface */
    private $repository;

    public function setUp()
    {
        $this->repository = m::mock(SupplierRepository::class);
        $this->service = new SupplierService($this->repository);
    }

    /**
     * @group Service
     */
    public function test_service_returns_supplier_options_sorted()
    {
        $this->repository->shouldReceive('findAll')
            ->andReturn([
                m::mock(Service::class)
                    ->shouldReceive('getId')
                    ->andReturn('c')->getMock()
                    ->shouldReceive('getName')
                    ->andReturn('C')->getMock(),
                m::mock(Service::class)
                    ->shouldReceive('getId')
                    ->andReturn('a')->getMock()
                    ->shouldReceive('getName')
                    ->andReturn('A')->getMock(),
                m::mock(Service::class)
                    ->shouldReceive('getId')
                    ->andReturn('b')->getMock()
                    ->shouldReceive('getName')
                    ->andReturn('B')->getMock(),
            ]);

        $this->assertEquals(
            [
                'a' => 'A',
                'b' => 'B',
                'c' => 'C',
            ],
            $this->service->getSupplierNames()
        );
    }
}
