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

namespace Surfnet\ServiceProviderDashboard\Tests\Integration\Application\CommandHandler\Entity;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Log\LoggerInterface;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\PushMetadataCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\Entity\PushMetadataCommandHandler;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\PushMetadataException;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Service\ManagePublishService;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class PushMetadataCommandHandlerTest extends MockeryTestCase
{
    /**
     * @var m\MockInterface&ManagePublishService
     */
    private $publishService;

    /**
     * @var m\MockInterface&LoggerInterface
     */
    private $logger;

    /**
     * @var m\MockInterface&FlashBagInterface
     */
    private $flashBag;

    /**
     * @var PushMetadataCommandHandler
     */
    private $commandHandler;

    public function setUp(): void
    {
        $this->publishService = m::mock(ManagePublishService::class);
        $this->logger = m::mock(LoggerInterface::class);
        $this->flashBag = m::mock(FlashBagInterface::class);

        $this->commandHandler = new PushMetadataCommandHandler(
            $this->publishService,
            $this->flashBag,
            $this->logger
        );
    }

    public function test_pushing_entities()
    {
        $this->publishService->shouldReceive('pushMetadata');
        $this->logger->shouldReceive('info')->with('Pushing metadata to EngineBlock using the test environment.');
        $this->assertNull($this->commandHandler->handle(new PushMetadataCommand('test')));
    }

    public function test_failed_push()
    {

        $e = new PushMetadataException('Foobar');
        $this->publishService
            ->shouldReceive('pushMetadata')
            ->andThrow($e);

        $this->logger->shouldReceive('info')->with('Pushing metadata to EngineBlock using the production environment.');
        $this->logger->shouldReceive('error')->with('Pushing to EngineBlock failed with message: "Foobar"');
        $this->flashBag->shouldReceive('add')->with('error', 'entity.edit.error.push');

        $this->assertNull($this->commandHandler->handle(new PushMetadataCommand('production')));
    }
}
