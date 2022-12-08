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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Infrastructure\DashboardBundle\Mailer;

use Exception;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Factory\MailMessageFactory;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Contracts\Component\Translation\TranslatorInterface;
use Twig\Environment as TwigEnvironment;

class MailMessageFactoryTest extends MockeryTestCase
{
    /**
     * @var TwigEnvironment|Mock
     */
    private $templateEngine;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var MailMessageFactory
     */
    private $factory;

    protected function setUp(): void
    {
        $this->templateEngine = m::mock(TwigEnvironment::class);
        $this->translator = m::mock(TranslatorInterface::class);

        $this->factory = new MailMessageFactory(
            'john@example.com',
            'doe@example.com',
            'no-reply@example.com',
            $this->translator,
            $this->templateEngine
        );
    }

    public function test_build_jira_ticket_creation_failed()
    {
        $this->translator
            ->shouldReceive('trans')
            ->andReturn('');

        $this->templateEngine
            ->shouldReceive('render');

        $e = m::mock(Exception::class);

        $e
            ->shouldReceive('getMessage')
            ->andReturn('Something went terribly wrong!');
        $e
            ->shouldReceive('getTrace')
            ->andReturn([]);

        $entity = m::mock(ManageEntity::class);
        $entity->shouldReceive('getService->getName')
            ->andReturn('ACME corporation');

        $entity->shouldReceive('getMetaData->getEntityId')
            ->andReturn('https://www.acme.com/metadata');

        $message = $this->factory->buildJiraIssueFailedMessage($e, $entity);

        $this->assertInstanceOf(TemplatedEmail::class, $message);
    }
}
