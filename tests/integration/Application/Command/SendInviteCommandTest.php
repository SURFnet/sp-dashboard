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

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Surfnet\ServiceProviderDashboard\Application\Command\Service\SendInviteCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\Service\SendInviteCommandHandler;
use Surfnet\ServiceProviderDashboard\Domain\Repository\Invite\SendInviteRepository;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SendInviteCommandTest extends MockeryTestCase
{

    private ValidatorInterface $validator;

    public function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public function testInvalidEmail(): void
    {
        $command = new SendInviteCommand(
            'invalid-email',
            'Test message',
            'en',
            1,
        );
        $violations = $this->validator->validate($command);

        $this->assertCount(1, $violations);
        $this->assertEquals('This value is not a valid email address.', $violations[0]->getMessage());
    }

    public function testBlankEmail(): void
    {
        $command = new SendInviteCommand(
            '',
            'Test message',
            'en',
            1,
        );
        $violations = $this->validator->validate($command);

        $this->assertCount(1, $violations);
        $this->assertEquals('This value should not be blank.', $violations[0]->getMessage());
    }

    public function testValidCommand(): void
    {
        $command = new SendInviteCommand(
            'test@example.com',
            'Test message',
            'en',
            1,
        );

        $violations = $this->validator->validate($command);
        $this->assertCount(0, $violations);
    }

    public function testBlankMessage(): void
    {
        $command = new SendInviteCommand(
            'test@example.com',
            '',
            'en',
            1,
        );

        $violations = $this->validator->validate($command);
        $this->assertCount(1, $violations);
        $this->assertEquals('message', $violations[0]->getPropertyPath());
    }

    public function testInvalidLanguage(): void
    {
        $command = new SendInviteCommand(
            'test@example.com',
            'Test message',
            'fr',
            1,
        );

        $violations = $this->validator->validate($command);
        $this->assertCount(1, $violations);
        $this->assertEquals('language', $violations[0]->getPropertyPath());
    }
}
