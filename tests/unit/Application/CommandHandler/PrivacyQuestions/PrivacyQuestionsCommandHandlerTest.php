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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Application\CommandHandler\PrivacyQuestions;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Log\LoggerInterface;
use Surfnet\ServiceProviderDashboard\Application\Command\PrivacyQuestions\PrivacyQuestionsCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\PrivacyQuestions\PrivacyQuestionsCommandHandler;
use Surfnet\ServiceProviderDashboard\Application\Exception\EntityNotFoundException;
use Surfnet\ServiceProviderDashboard\Domain\Entity\PrivacyQuestions;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Repository\PrivacyQuestionsRepository;

class PrivacyQuestionsCommandHandlerTest extends MockeryTestCase
{
    /**
     * @var PrivacyQuestionsCommandHandler
     */
    private $commandHandler;

    /**
     * @var PrivacyQuestionsRepository|m\MockInterface
     */
    private $repository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function setUp(): void
    {
        $this->repository = m::mock(PrivacyQuestionsRepository::class);
        $this->logger = m::mock(LoggerInterface::class);
        $this->commandHandler = new PrivacyQuestionsCommandHandler($this->repository, $this->logger);
    }

    public function test_it_can_save_a_new_privacy_questions()
    {
        $service = m::mock(Service::class);
        $command = PrivacyQuestionsCommand::fromService($service);

        $this->repository
            ->shouldReceive('save')
            ->once();

        $this->commandHandler->handle($command);
    }

    public function test_it_can_save_an_existing_privacy_questions()
    {
        $service = m::mock(Service::class);

        $questions = m::mock(PrivacyQuestions::class)
            ->makePartial();

        $questions->shouldReceive('getService')
            ->andReturn($service);

        $command = PrivacyQuestionsCommand::fromQuestions($questions);

        $this->repository
            ->shouldReceive('findByService')
            ->with($service)
            ->andReturn($questions)
            ->once();

        $this->repository
            ->shouldReceive('save')
            ->once();

        $this->commandHandler->handle($command);
    }

    public function test_it_throws_exception_when_questions_entity_not_found()
    {
        $service = m::mock(Service::class);

        $questions = m::mock(PrivacyQuestions::class)
            ->makePartial();

        $questions
            ->shouldReceive('getService')
            ->andReturn($service);

        $command = PrivacyQuestionsCommand::fromQuestions($questions);

        $service
            ->shouldReceive('getName')
            ->andReturn('Foobar');

        $this->repository
            ->shouldReceive('findByService')
            ->with($service)
            ->andReturn(null)
            ->once();

        $this->logger
            ->shouldReceive('error')
            ->with('Unable to fetch the privacy questions for "Foobar"');

        $this->expectExceptionMessage("Unable to find privacy question");
        $this->expectException(EntityNotFoundException::class);
        $this->commandHandler->handle($command);
    }
}
