<?php

/**
 * Copyright 2018 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Infrastructure\Jira\Factory;

use Mockery as m;
use Mockery\Mock;
use PHPUnit\Framework\TestCase;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Ticket;
use Surfnet\ServiceProviderDashboard\Infrastructure\Jira\Factory\IssueFieldFactory;
use Symfony\Component\Translation\TranslatorInterface;

class IssueFieldFactoryTest extends TestCase
{
    /**
     * @var IssueFieldFactory
     */
    private $factory;
    /**
     * @var TranslatorInterface|Mock
     */
    private $translator;

    public function setUp()
    {
        $this->translator = m::mock(TranslatorInterface::class);
        $this->factory = new IssueFieldFactory(
            'customfield_10107',
            'customfield_10108',
            'customfield_99999',
            'Critical',
            'SPD',
            $this->translator
        );
    }

    public function test_build_issue_field_from_ticket()
    {
        $ticket = new Ticket(
            'https://example.com',
            'manage-id',
            'Test Service',
            'arbitrary-summary-key',
            'arbitrary-description-key',
            'John Doe',
            'john@example.com',
            'arbitrary-issue-type'
        );

        $this->translator
            ->shouldReceive('trans')
            ->with(
                'arbitrary-description-key',
                [
                    '%applicant_name%' => 'John Doe',
                    '%applicant_email%' => 'john@example.com',
                    '%entity_name%' => 'Test Service',
                ]
            )
            ->andReturn('Description')
            ->once();

        $this->translator
            ->shouldReceive('trans')
            ->with('arbitrary-summary-key', ['%entity_name%' => 'Test Service'])
            ->andReturn('Summary')
            ->once();

        $issueField = $this->factory->fromTicket($ticket);

        $this->assertEquals('Summary', $issueField->summary);
        $this->assertEquals('Description', $issueField->description);
        // The custom field is used for saving the entity id.
        $this->assertEquals('https://example.com', $issueField->customFields['customfield_10107']);
        $this->assertEquals('John Doe, (john@example.com)', $issueField->customFields['customfield_99999']);
        $this->assertEquals('arbitrary-issue-type', $ticket->getIssueType());
        $this->assertEquals('Critical', $issueField->priority->name);
    }
}
