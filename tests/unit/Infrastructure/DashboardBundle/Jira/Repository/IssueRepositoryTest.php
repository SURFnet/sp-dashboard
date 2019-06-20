<?php
/**
 * Copyright 2019 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Infrastructure\DashboardBundle\Jira\Repository;

use PHPUnit_Framework_TestCase;
use Surfnet\ServiceProviderDashboard\Application\Service\TicketServiceInterface;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Issue;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Ticket;
use Surfnet\ServiceProviderDashboard\Infrastructure\Jira\Repository\DevelopmentIssueRepository;

class IssueRepositoryTest extends PHPUnit_Framework_TestCase
{
    const CACHE_FILEPATH =  __DIR__ . '/issues.json';

    /**
     * @var TicketServiceInterface
     */
    private $repository;

    public function setup()
    {
        @unlink(self::CACHE_FILEPATH);
        $this->repository = new DevelopmentIssueRepository(self::CACHE_FILEPATH);
    }

    public function tearDown()
    {
        @unlink(self::CACHE_FILEPATH);
    }

    public function test_repository_flow()
    {
        $tickets = [
            ['id' => 0, 'issueType' => 'test'],
            ['id' => 1, 'issueType' => 'test'],
            ['id' => 2, 'issueType' => 'test'],
            ['id' => 3, 'issueType' => 'test'],
            ['id' => 4, 'issueType' => 'test'],
            ['id' => 5, 'issueType' => 'test2'],
            ['id' => 6, 'issueType' => 'test2'],
            ['id' => 7, 'issueType' => 'test3'],
        ];

        // save issues
        foreach ($tickets as $ticket) {
            $ticket = $this->createTicket($ticket['id'], $ticket['issueType']);
            $this->repository->createIssueFrom($ticket);
        }


        // remove all issues with id < 3
        foreach ($tickets as $ticket) {
            if ($ticket['id'] < 4) {
                $issue = $this->repository->findByManageId('manageId-' . $ticket['id']);
                $this->repository->delete($issue->getKey());
            }
        }

        // should find nothing, wrong issueType
        $issue = $this->repository->findByManageIdAndIssueType('manageId-4', 'test3');
        $this->assertSame(null, $issue);

        // should find one issue
        $issue = $this->repository->findByManageIdAndIssueType('manageId-4', 'test');
        $this->assertInstanceOf(Issue::class, $issue);
        $this->assertSame('manageId-4', $issue->getKey());
        $this->assertSame('test', $issue->getIssueType());

        // should not find one issue, unknown manageId
        $issue = $this->repository->findByManageId('manageId');
        $this->assertSame(null, $issue);

        // should return two results, manageId not set and manageId-2 removed
        $results = $this->repository->findByManageIds(['manageId', 'manageId-2', 'manageId-4', 'manageId-5']);
        $this->assertCount(2, $results);

        // assert the two issues
        $issue = $results->getIssueById('manageId-4');
        $this->assertSame('manageId-4', $issue->getKey());
        $this->assertSame('test', $issue->getIssueType());
        $issue = $results->getIssueById('manageId-5');
        $this->assertSame('manageId-5', $issue->getKey());
        $this->assertSame('test2', $issue->getIssueType());

        // delete all issues left
        $found = 0;
        foreach ($tickets as $ticket) {
            $issue = $this->repository->findByManageId('manageId-' . $ticket['id']);
            if ($issue) {
                $found++;
                $this->repository->delete($issue->getKey());
            }
        }
        $this->assertSame(4, $found);
    }

    /**
     * @param $id
     * @param $issueType
     * @return Ticket
     */
    private function createTicket($id, $issueType)
    {
        return new Ticket(
            'entityId-'.$id,
            'manageId-'.$id,
            'nameEn-'.$id,
            'summaryTranslationKey-'.$id,
            'descriptionTranslationKey-'.$id,
            'applicantName-'.$id,
            'applicantEmail-'.$id,
            $issueType
        );
    }
}
