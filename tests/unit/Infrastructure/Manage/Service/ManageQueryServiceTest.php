<?php

/**
 * Copyright 2020 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Infrastructure\Manage\Service;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\Mock;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\QueryClient;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Service\MangeQueryService;

class ManageQueryServiceTest extends MockeryTestCase
{
    private $queryService;
    /**
     * @var Mock&QueryClient
     */
    private $testClient;
    /**
     * @var Mock&QueryClient
     */
    private $productionClient;

    protected function setUp(): void
    {
        $this->testClient = m::mock(QueryClient::class);
        $this->productionClient = m::mock(QueryClient::class);
        $this->queryService = new MangeQueryService($this->testClient, $this->productionClient);
    }

    public function test_calling_find_method_on_service_on_test_client()
    {
        $this->testClient
            ->shouldReceive('findByManageId')
            ->with('my-manage-id')
            ->once();

        $this->queryService
            ->findByManageId('test', 'my-manage-id');
    }
    public function test_calling_find_method_on_service_on_prod_client()
    {
        $this->productionClient
            ->shouldReceive('findByManageId')
            ->with('my-manage-id')
            ->once();

        $this->queryService
            ->findByManageId('production', 'my-manage-id');
    }

    public function test_method_calling_without_setting_mode()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Please set the query mode to one of these environments (test, production)');
        $this->queryService->findByManageId('prod', 'my-manage-id');
    }
}
