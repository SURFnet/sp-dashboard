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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Application\Provider;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Surfnet\ServiceProviderDashboard\Application\Provider\EntityQueryRepositoryProvider;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\QueryClient as ManageQueryClient;

class EntityQueryRepositoryProviderTest extends MockeryTestCase
{
    /**
     * @var ManageQueryClient
     */
    private $manageTestQueryClient;

    /**
     * @var ManageQueryClient
     */
    private $manageProductionQueryClient;

    /**
     * @var EntityQueryRepositoryProvider
     */
    private $provider;


    public function setUp()
    {
        $this->manageTestQueryClient = m::mock(ManageQueryClient::class);
        $this->manageProductionQueryClient = m::mock(ManageQueryClient::class);

        $this->provider = new EntityQueryRepositoryProvider(
            $this->manageTestQueryClient,
            $this->manageProductionQueryClient
        );
    }

    public function test_get_from_environment()
    {
        $testClient = $this->provider->fromEnvironment('test');
        $productionClient = $this->provider->fromEnvironment('production');
        $this->assertEquals($this->manageTestQueryClient, $testClient);
        $this->assertEquals($this->manageProductionQueryClient, $productionClient);
    }

    public function test_reject_invalid_environment()
    {
        $this->expectException(\Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage("Unsupported environment \"fest\" requested");
        $this->provider->fromEnvironment('fest');
    }

    public function test_getters()
    {
        $this->assertInstanceOf(ManageQueryClient::class, $this->provider->getManageProductionQueryClient());
        $this->assertInstanceOf(ManageQueryClient::class, $this->provider->getManageTestQueryClient());
    }
}
