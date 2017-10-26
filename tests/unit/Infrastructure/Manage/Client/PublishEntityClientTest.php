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
use Mockery\Mock;
use Psr\Log\LoggerInterface;
use Surfnet\ServiceProviderDashboard\Application\Metadata\GeneratorInterface;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\PublishEntityClient;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Http\HttpClient;

class PublishEntityClientTest extends MockeryTestCase
{
    /**
     * @var PublishEntityClient
     */
    private $client;

    /**
     * @var MockHandler
     */
    private $mockHandler;

    /**
     * @var LoggerInterface|Mock
     */
    private $logger;

    /**
     * @var GeneratorInterface|Mock
     */
    private $generator;

    public function setUp()
    {
        $this->mockHandler = new MockHandler();
        $guzzle = new Client(['handler' => $this->mockHandler]);

        $this->generator = m::mock(GeneratorInterface::class);

        $this->logger = m::mock(LoggerInterface::class);

        $this->client = new PublishEntityClient(new HttpClient($guzzle), $this->generator, $this->logger);
    }

    public function test_it_can_publish_to_manage()
    {
        $this->mockHandler->append(new Response(200, [], json_encode(['id' => '25055635-8c2c-4f54-95a6-68891a554e95'])));
        $this->mockHandler->append(new Response(200, [], json_encode(['test' => 'OK'])));

        $xml = file_get_contents(__DIR__ . '/fixture/metadata.xml');

        $entity = m::mock(Entity::class);
        $entity
            ->shouldReceive('getMetadataXml')
            ->andReturn($xml);

        $entity
            ->shouldReceive('getComments')
            ->andReturn('Lorem ipsum dolor sit');

        $this->logger
            ->shouldReceive('info');

        $this->generator
            ->shouldReceive('generate')
            ->andReturn($xml);

        $response = $this->client->publish($entity);
        $this->assertEquals('OK', $response['test']);
    }

    /**
     * @expectedException \Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Exception\PublishMetadataException
     * @expectedExceptionMessage Unable to publish the metadata to Manage
     */
    public function test_it_handles_failing_publish_action()
    {
        $this->mockHandler->append(new Response(418));

        $xml = file_get_contents(__DIR__ . '/fixture/metadata.xml');

        $entity = m::mock(Entity::class);
        $entity
            ->shouldReceive('getMetadataXml')
            ->andReturn(file_get_contents(__DIR__ . '/fixture/metadata.xml'));

        $this->generator
            ->shouldReceive('generate')
            ->andReturn($xml);

        $this->client->publish($entity);
    }

    public function test_it_can_push_to_engineblock()
    {
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
