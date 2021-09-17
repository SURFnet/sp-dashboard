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

namespace Infrastructure\Manage\Service;

use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\PublishEntityClient;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\Mock;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Service\ManagePublishService;

class ManagePublishServiceTest extends MockeryTestCase
{
    private $publishService;
    /**
     * @var Mock&PublishEntityClient
     */
    private $productionClient;

    protected function setUp(): void
    {
        $testClient = m::mock(PublishEntityClient::class);
        $this->productionClient = m::mock(PublishEntityClient::class);
        $this->publishService = new ManagePublishService($testClient, $this->productionClient);
    }

    public function test_calling_push_to_production()
    {
        $this->productionClient
            ->shouldReceive('pushMetadata')
            ->once();

        $this->publishService
            ->pushMetadata('production');
    }

    public function test_unknown_environment()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Please set the publication mode to one of these environments (test, production)'
        );
        $this->publishService->pushMetadata('ahkh-morpork');
    }
}
