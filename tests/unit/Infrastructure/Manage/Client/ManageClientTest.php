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

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\PublishServiceClient;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Http\HttpClient;

class ManageClientTest extends MockeryTestCase
{
    /**
     * @var PublishServiceClient
     */
    private $client;

    /**
     * @var MockHandler
     */
    private $mockHandler;

    public function setUp()
    {
        $this->markTestSkipped('The API is not ready to be consumed yet. Awaiting Okke\'s changes.');
        $this->mockHandler = new MockHandler();
        $guzzle = new Client(['handler' => $this->mockHandler]);
        $this->client = new PublishServiceClient(new HttpClient($guzzle));
    }

    public function test_it_can_publish_to_manage()
    {
        // First call represends the 'xml to json' POST on the Manage endpoint
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__ . '/fixture/metadata.json')));
        // Second call is the response returned from Manage after adding the service to the registry
        $this->mockHandler->append(new Response(200, [], json_encode(['test' => 'OK'])));

        $service = m::mock(Service::class);
        $service
            ->shouldReceive('getMetadataXml')
            ->andReturn(file_get_contents(__DIR__ . '/fixture/metadata.xml'));
        $response = $this->client->publish($service);
        $this->assertEquals('OK', $response['test']);
    }
}
