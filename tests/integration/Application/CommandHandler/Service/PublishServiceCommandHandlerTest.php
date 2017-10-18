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

namespace Surfnet\ServiceProviderDashboard\Tests\Integration\Application\CommandHandler\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\Mock;
use Psr\Log\LoggerInterface;
use Surfnet\ServiceProviderDashboard\Application\Command\Service\PublishServiceCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\Service\PublishServiceCommandHandler;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Repository\ServiceRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\PublishServiceClient;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Http\HttpClient;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class PublishServiceCommandHandlerTest extends MockeryTestCase
{

    /**
     * @var PublishServiceCommandHandler
     */
    private $commandHandler;

    /**
     * @var ServiceRepository|Mock
     */
    private $repository;

    /**
     * @var MockHandler
     */
    private $mockHandler;

    /**
     * @var LoggerInterface|Mock
     */
    private $logger;

    /**
     * @var FlashBagInterface|Mock
     */
    private $flashBag;

    public function setUp()
    {
        $this->repository = m::mock(ServiceRepository::class);

        $this->mockHandler = new MockHandler();
        $guzzle = new Client(['handler' => $this->mockHandler]);
        $client = new PublishServiceClient(new HttpClient($guzzle));

        $this->logger = m::mock(LoggerInterface::class);
        $this->flashBag = m::mock(FlashBagInterface::class);

        $this->commandHandler = new PublishServiceCommandHandler(
            $this->repository,
            $client,
            $this->logger,
            $this->flashBag
        );
    }

    public function test_it_can_publish_to_manage()
    {
        $service = m::mock(Service::class);
        $service
            ->shouldReceive('getMetadataXml')
            ->andReturn(file_get_contents(__DIR__ . '/fixture/metadata.xml'));

        $service
            ->shouldReceive('getNameNl')
            ->andReturn('Test Service Name');

        $this->repository
            ->shouldReceive('findById')
            ->with('d6f394b2-08b1-4882-8b32-81688c15c489')
            ->andReturn($service);

        $this->logger
            ->shouldReceive('info')
            ->times(2);

        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__ . '/fixture/metadata.json')));
        $this->mockHandler->append(new Response(200, [], '{"id": "d6f394b2-08b1-4882-8b32-81688c15c489"}'));
        $this->mockHandler->append(new Response(200, [], '{"status":"OK"}'));

        $command = new PublishServiceCommand('d6f394b2-08b1-4882-8b32-81688c15c489');
        $this->commandHandler->handle($command);
    }

    public function test_it_handles_failing_push()
    {
        $service = m::mock(Service::class);
        $service
            ->shouldReceive('getMetadataXml')
            ->andReturn(file_get_contents(__DIR__ . '/fixture/metadata.xml'));

        $service
            ->shouldReceive('getNameNl')
            ->andReturn('Test Service Name');

        $this->repository
            ->shouldReceive('findById')
            ->with('d6f394b2-08b1-4882-8b32-81688c15c489')
            ->andReturn($service);

        $this->logger
            ->shouldReceive('info')
            ->times(2);

        $this->logger
            ->shouldReceive('error')
            ->times(1);

        $this->flashBag
            ->shouldReceive('add')
            ->with('error', 'service.edit.error.push');

        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__ . '/fixture/metadata.json')));
        $this->mockHandler->append(new Response(200, [], '{"id": "d6f394b2-08b1-4882-8b32-81688c15c489"}'));
        $this->mockHandler->append(new Response(418));

        $command = new PublishServiceCommand('d6f394b2-08b1-4882-8b32-81688c15c489');
        $this->commandHandler->handle($command);
    }

    public function test_it_handles_failing_publish()
    {
        $service = m::mock(Service::class);
        $service
            ->shouldReceive('getMetadataXml')
            ->andReturn(file_get_contents(__DIR__ . '/fixture/metadata.xml'));

        $service
            ->shouldReceive('getNameNl')
            ->andReturn('Test Service Name');

        $this->repository
            ->shouldReceive('findById')
            ->with('d6f394b2-08b1-4882-8b32-81688c15c489')
            ->andReturn($service);

        $this->logger
            ->shouldReceive('info')
            ->times(1);

        $this->logger
            ->shouldReceive('error')
            ->times(1);

        $this->flashBag
            ->shouldReceive('add')
            ->with('error', 'service.edit.error.publish');

        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__ . '/fixture/metadata.json')));
        $this->mockHandler->append(new Response(418));

        $command = new PublishServiceCommand('d6f394b2-08b1-4882-8b32-81688c15c489');
        $this->commandHandler->handle($command);
    }
}
