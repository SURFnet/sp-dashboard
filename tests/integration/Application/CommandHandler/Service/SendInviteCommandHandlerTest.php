<?php

/**
 * Copyright 2024 SURFnet B.V.
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

use DateTime;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Log\NullLogger;
use Surfnet\ServiceProviderDashboard\Application\Command\Service\SendInviteCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\Service\SendInviteCommandHandler;
use Surfnet\ServiceProviderDashboard\Domain\Repository\Invite\SendInviteRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\InviteException;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SendInviteCommandHandlerTest extends MockeryTestCase
{

    private SendInviteCommandHandler $commandHandler;

    private TranslatorInterface $translator;

    private ValidatorInterface $validator;

    public function setUp(): void
    {
        $inviteRepository = m::mock(SendInviteRepository::class);
        $this->translator = m::mock(TranslatorInterface::class);

        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $this->commandHandler = new SendInviteCommandHandler(
            $inviteRepository,
            $this->validator,
            new NullLogger(),
        );
    }

    public function testValidatesCommand(): void
    {
        $command = new SendInviteCommand(
            'invalid-email',
            'Test message',
            'en',
            1,
            new DateTime('+1 day'),
            new DateTime('+1 week')
        );

        $this->expectException(InviteException::class);
        $this->expectExceptionMessage('Could not create invite, validation failed: This value is not a valid email address.');

        $this->commandHandler->handle($command);
    }

}
