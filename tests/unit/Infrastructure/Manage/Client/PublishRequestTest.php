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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Infrastructure\DashboardBundle\Service;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\PublishRequest;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\PublishServiceRepository;

class PublishRequestTest extends MockeryTestCase
{
    public function test_it_can_be_created_from_service_entity()
    {
        $service = m::mock(Service::class);
        $service->shouldReceive('getMetadataXml')->andReturn('<md></md>');
        $publishRequest = PublishRequest::from($service);
        $this->assertInstanceOf(PublishRequest::class, $publishRequest);
        $this->assertEquals('<md></md>', $publishRequest->metadataXml);
    }
}
