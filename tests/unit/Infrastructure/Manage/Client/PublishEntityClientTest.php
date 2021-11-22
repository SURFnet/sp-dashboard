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

namespace Infrastructure\Manage\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\Mock;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Surfnet\ServiceProviderDashboard\Application\Metadata\JsonGeneratorStrategy;
use Surfnet\ServiceProviderDashboard\Application\ViewObject\Apis\ApiConfig as Config;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\PublishMetadataException;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\PushMetadataException
    as PushMetadataExceptionAlias;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\PublishEntityClient;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\ManageClient;

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
     * @var Config|Mock
     */
    private $manageConfig;

    /**
     * @var LoggerInterface|Mock
     */
    private $logger;

    /**
     * @var JsonGeneratorStrategy|Mock
     */
    private $generator;

    public function setUp()
    {
        $this->mockHandler = new MockHandler();
        $guzzle = new Client(['handler' => $this->mockHandler]);

        $this->generator = m::mock(JsonGeneratorStrategy::class);

        $this->logger = m::mock(LoggerInterface::class);

        $this->manageConfig = m::mock(Config::class);

        $this->client = new PublishEntityClient(
            new ManageClient(
                $guzzle,
                new NullLogger()
            ),
            $this->generator,
            $this->manageConfig,
            $this->logger
        );
    }

    public function test_it_can_publish_to_manage()
    {
        $this->mockHandler->append(new Response(200, [], json_encode(['id' => '1'])));

        $json = file_get_contents(__DIR__ . '/fixture/metadata.json');

        $entity = m::mock(ManageEntity::class);
        $entity
            ->shouldReceive('getMetaData->getEntityId')
            ->andReturn('http://test');

        $entity
            ->shouldReceive('getId')
            ->andReturn(null);

        $this->manageConfig
            ->shouldReceive('getPublicationStatus->getStatus')
            ->andReturn('testaccepted')
            ->once();

        $this->logger
            ->shouldReceive('info');

        $this->generator
            ->shouldReceive('generateForNewEntity')
            ->andReturn($json);

        $response = $this->client->publish($entity);
        $this->assertEquals('1', $response['id']);
    }

    public function test_it_can_update_to_manage()
    {
        $this->mockHandler->append(new Response(200, [], json_encode(['id' => '1'])));

        $json = file_get_contents(__DIR__ . '/fixture/metadata.json');

        $entity = m::mock(ManageEntity::class);
        $entity
            ->shouldReceive('getMetaData->getEntityId')
            ->andReturn('http://test');

        $entity
            ->shouldReceive('getId')
            ->andReturn('25055635-8c2c-4f54-95a6-68891a554e95');

        $this->manageConfig
            ->shouldReceive('getPublicationStatus->getStatus')
            ->andReturn('prodaccepted')
            ->once();

        $this->logger
            ->shouldReceive('info');

        $this->generator
            ->shouldReceive('generateForExistingEntity')
            ->andReturn($json);

        $response = $this->client->publish($entity);
        $this->assertEquals('1', $response['id']);
    }

    public function test_it_handles_failing_publish_action()
    {
        $this->expectExceptionMessage("Unable to publish the metadata to Manage");
        $this->expectException(PublishMetadataException::class);

        $this->mockHandler->append(new Response(418));

        $json = file_get_contents(__DIR__ . '/fixture/metadata.json');

        $entity = m::mock(ManageEntity::class);
        $entity
            ->shouldReceive('getMetaData->getEntityId')
            ->andReturn('http://test');
        $entity
            ->shouldReceive('getMetadataUrl')
            ->andReturn('https://fobar.example.com');
        $entity
            ->shouldReceive('getId')
            ->andReturn(null);

        $this->manageConfig
            ->shouldReceive('getPublicationStatus->getStatus')
            ->andReturn('prodaccepted')
            ->once();

        $this->logger
            ->shouldReceive('info');

        $this->logger
            ->shouldReceive('error');

        $this->generator
            ->shouldReceive('generateForNewEntity')
            ->andReturn($json);

        $this->client->publish($entity);
    }

    public function test_it_can_push_to_engineblock()
    {
        $this->mockHandler->append(new Response(200, [], '{"status":"OK"}'));

        $response = $this->client->pushMetadata();
        $this->assertEquals('OK', $response['status']);
    }

    public function test_it_handles_failing_push_action()
    {
        $this->expectExceptionMessage("Unable to push the metadata to Engineblock");
        $this->expectException(PushMetadataExceptionAlias::class);
        $this->logger
            ->shouldReceive('error')
            ->once();
        // First call represents the 'xml to json' POST on the Manage endpoint
        $this->mockHandler->append(new Response(418));
        $this->client->pushMetadata();
    }

    public function test_it_handles_failing_push_action_with_response()
    {
        $this->expectExceptionMessage("Pushing did not succeed");
        $this->expectException(PushMetadataExceptionAlias::class);
        // First call represents the 'xml to json' POST on the Manage endpoint
        $this->mockHandler->append(new Response(200, [], '{"status": "failed", "validation": "invalid enum"}'));

        $this->logger
            ->shouldReceive('error')
            ->once();

        $this->client->pushMetadata();
    }
}
