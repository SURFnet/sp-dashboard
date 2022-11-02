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
use Surfnet\ServiceProviderDashboard\Domain\Entity\Contact as Applicant;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\ConnectionRequest;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Ticket;
use Surfnet\ServiceProviderDashboard\Infrastructure\Jira\Factory\IssueFieldFactory;
use Symfony\Component\Translation\TranslatorInterface;

class IssueFieldConnectionRequestTest extends TestCase
{
    /**
     * @var IssueFieldFactory
     */
    private $factory;
    /**
     * @var TranslatorInterface|Mock
     */
    private $translator;

    protected static $translation;

    public function setUp()
    {
        $this->translator = m::mock(TranslatorInterface::class);
        $this->factory = new IssueFieldFactory(
            'customfield_13018',
            'customfield_13401',
            'customfield_99999',
            'Critical',
            'SPD',
            $this->translator
        );
    }

    public function test_build_issue_field_from_connection_requests_ticket()
    {
        $connectionRequests = [];

        $manageEntity = m::mock(ManageEntity::class);
        $manageEntity
            ->shouldReceive('getId')
            ->andReturn('b05664d5-1ba3-483b-8d4f-1a6c03b48bc4');

        $manageEntity
            ->shouldReceive('getMetaData->getNameEn')
            ->andReturn('Test Entity Name');

        $manageEntity
            ->shouldReceive('getMetaData->getEntityId')
            ->andReturn('https://app.example.com/');


        $applicant = m::mock(Applicant::class);
        $applicant->shouldReceive('getEmailAddress')->andReturn('j.doe@example.com');
        $applicant->shouldReceive('getDisplayName')->andReturn('john doe');

        $connectionRequest1 = new ConnectionRequest();
        $connectionRequest1->institution = 'Institution-1';
        $connectionRequest1->name = 'Contact person 1';
        $connectionRequest1->email = 'cp1@example.com';

        $connectionRequest2 = new ConnectionRequest();
        $connectionRequest2->institution = 'Institution-2';
        $connectionRequest2->name = 'Contact person 2';
        $connectionRequest2->email = 'cp2@example.com';

        $connectionRequests[] = $connectionRequest1;
        $connectionRequests[] = $connectionRequest2;


        $ticket = Ticket::fromConnectionRequests(
            $manageEntity,
            $applicant,
            $connectionRequests,
            'SPD-IdP-invite',
            'entity.connection_request.ticket.summary',
            'entity.connection_request.ticket.description'
        );

        $this->translator
            ->shouldReceive('trans')
            ->with(
                'entity.connection_request.ticket.applicant',
                [
                    '%applicant_name%' => 'john doe',
                    '%applicant_email%' => 'j.doe@example.com',
                    '%entity_name%' => 'Test Entity Name'
                ]
            )
            ->andReturn('applicant - ')
            ->once();

        $this->translator
            ->shouldReceive('trans')
            ->with(
                'entity.connection_request.ticket.institution.header',
                []
            )
            ->andReturn('institution header - ')
            ->once();

        $this->translator
            ->shouldReceive('trans')
            ->with(
                'entity.connection_request.ticket.institution.body',
                [
                    '%institution_name%' => 'Institution-1',
                    '%contact_name%' => 'Contact person 1',
                    '%contact_email%' => 'cp1@example.com'
                ]
            )
            ->andReturn('institution 1 - ')
            ->once();

        $this->translator
            ->shouldReceive('trans')
            ->with(
                'entity.connection_request.ticket.institution.body',
                [
                    '%institution_name%' => 'Institution-2',
                    '%contact_name%' => 'Contact person 2',
                    '%contact_email%' => 'cp2@example.com'
                ]
            )
            ->andReturn('institution 2')
            ->once();

        $this->translator
            ->shouldReceive('trans')
            ->with('entity.connection_request.ticket.summary', ['%entity_name%' => 'Test Entity Name'])
            ->andReturn('Title')
            ->once();

        $issueField = $this->factory->fromConnectionRequestTicket($ticket);

        $this->assertEquals('applicant - institution header - institution 1 - institution 2', $issueField->description);
        $this->assertEquals('Title', $issueField->summary);
        $this->assertEquals('b05664d5-1ba3-483b-8d4f-1a6c03b48bc4', $issueField->customFields['customfield_13401']);
        $this->assertEquals('https://app.example.com/', $issueField->customFields['customfield_13018']);
    }
}
