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

class PublishServiceClientTest extends MockeryTestCase
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
        $this->mockHandler = new MockHandler();
        $guzzle = new Client(['handler' => $this->mockHandler]);
        $this->client = new PublishServiceClient(new HttpClient($guzzle));
    }

    public function test_it_can_publish_to_manage()
    {
        // First call represents the 'xml to json' POST on the Manage endpoint
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

    /**
     * @expectedException \Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Exception\ConvertMetadataException
     * @expectedExceptionMessage Unable to convert the XML metadata to JSON
     */
    public function test_it_handles_failing_conversion()
    {
        // First call represents the 'xml to json' POST on the Manage endpoint
        $this->mockHandler->append(new Response(418));

        $service = m::mock(Service::class);
        $service
            ->shouldReceive('getMetadataXml')
            ->andReturn(file_get_contents(__DIR__ . '/fixture/metadata.xml'));
        $this->client->publish($service);
    }

    /**
     * @expectedException \Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Exception\PublishMetadataException
     * @expectedExceptionMessage Unable to publish the metadata to Manage
     */
    public function test_it_handles_failing_publish_action()
    {
        // First call represents the 'xml to json' POST on the Manage endpoint
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__ . '/fixture/metadata.json')));
        // The second call fails (publish to manage)
        $this->mockHandler->append(new Response(418));

        $service = m::mock(Service::class);
        $service
            ->shouldReceive('getMetadataXml')
            ->andReturn(file_get_contents(__DIR__ . '/fixture/metadata.xml'));
        $this->client->publish($service);
    }

    public function test_it_can_push_to_engineblock()
    {
        // First call represents the 'xml to json' POST on the Manage endpoint
        $this->mockHandler->append(new Response(200, [], '{"status":"OK"}'));

        $response = $this->client->pushMetadata();
        $this->assertEquals('OK', $response['status']);
    }

    /**
     * @expectedException \Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Exception\PushMetadataException
     * @expectedExceptionMessage Unable to push the metadata to Engineblock
     */
    public function test_it_handles_failing_push_action()
    {
        // First call represents the 'xml to json' POST on the Manage endpoint
        $this->mockHandler->append(new Response(418));
        $this->client->pushMetadata();
    }

    /**
     * @expectedException \Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Exception\PushMetadataException
     * @expectedExceptionMessage Pushing did not succeed
     */
    public function test_it_handles_failing_push_action_with_response()
    {
        // First call represents the 'xml to json' POST on the Manage endpoint
        $this->mockHandler->append(new Response(200, [], '{"status": "failed", "validation": "invalid enum"}'));

        $service = m::mock(Service::class);
        $service
            ->shouldReceive('getMetadataXml')
            ->andReturn(file_get_contents(__DIR__ . '/fixture/metadata.xml'));
        $this->client->pushMetadata($service);
    }
}
