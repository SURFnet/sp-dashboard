<?php

/**
 * Copyright 2022 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Webtests;

use Mockery as m;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Contact as Applicant;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\ConnectionRequest;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Ticket;
use Surfnet\ServiceProviderDashboard\Infrastructure\Jira\Factory\IssueFieldFactory;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as SymfonyWebTestCase;

class IssueFieldFactoryTest extends SymfonyWebTestCase
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Client
     */
    private $client;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function setUp()
    {
        $this->client = static::createClient(
            [],
            [
                'HTTPS' => 'on',
            ]
        );

        $this->client->disableReboot();
        $this->translator = $this->client
            ->getContainer()
            ->get('translator');
    }

    protected function tearDown()
    {
        m::close();
    }

    public function test_it_translates_the_connection_request_ticket()
    {
        $connectionRequests = [];

        $manageEntity = $this->getManageEntity();

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

        $factory = new IssueFieldFactory(
            'anything',
            'anything',
            'anything',
            'anything',
            'anything',
            $this->translator
        );

        $ticket = Ticket::fromConnectionRequests(
            $manageEntity,
            $applicant,
            $connectionRequests,
            'SPD-IdP-invite',
            'entity.connection_request.ticket.summary',
            'entity.connection_request.ticket.description'
        );

        $issueField = $factory->fromConnectionRequestTicket($ticket);

        $this->assertEquals("h2. Details\n*Applicant name*: john doe *Applicant email*: j.doe@example.com. *Entity name (en)*: Test Entity Name.\nInstitution: Institution-1 Contact name: Contact person 1 Contact email: cp1@example.com\nInstitution: Institution-2 Contact name: Contact person 2 Contact email: cp2@example.com\n", $issueField->description);
    }

    public function test_it_translates_empty_connection_request_ticket()
    {
        $connectionRequests = [];

        $manageEntity = $this->getManageEntity();

        $applicant = m::mock(Applicant::class);
        $applicant->shouldReceive('getEmailAddress')->andReturn('j.doe@example.com');
        $applicant->shouldReceive('getDisplayName')->andReturn('john doe');

        $factory = new IssueFieldFactory(
            'anything',
            'anything',
            'anything',
            'anything',
            'anything',
            $this->translator
        );

        $ticket = Ticket::fromConnectionRequests(
            $manageEntity,
            $applicant,
            $connectionRequests,
            'SPD-IdP-invite',
            'entity.connection_request.ticket.summary',
            'entity.connection_request.ticket.description'
        );

        $issueField = $factory->fromConnectionRequestTicket($ticket);

        $this->assertEquals("h2. Details\n*Applicant name*: john doe *Applicant email*: j.doe@example.com. *Entity name (en)*: Test Entity Name.\n", $issueField->description);
    }

    /**
     * @return m\LegacyMockInterface|m\MockInterface|ManageEntity|ManageEntity&m\LegacyMockInterface|ManageEntity&m\MockInterface
     */
    private function getManageEntity(): ManageEntity
    {
        $manageEntity = m::mock(ManageEntity::class)->makePartial();
        $manageEntity
            ->shouldReceive('getId')
            ->once()
            ->andReturn('b05664d5-1ba3-483b-8d4f-1a6c03b48bc4');

        $manageEntity
            ->shouldReceive('getMetaData->getNameEn')
            ->andReturn('Test Entity Name');

        $manageEntity
            ->shouldReceive('getMetaData->getEntityId')
            ->andReturn('https://app.example.com/');
        return $manageEntity;
    }
}
