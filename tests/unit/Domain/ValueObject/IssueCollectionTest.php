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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Domain\ValueObject;

use PHPUnit\Framework\TestCase;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Issue;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\IssueCollection;

class IssueCollectionTest extends TestCase
{
    public function test_can_be_used_with_empty_issue_set()
    {
        $collection = new IssueCollection([]);
        self::assertEquals(0, $collection->count());
    }

    public function test_get_issue_by_key()
    {
        $issue1 = new Issue('CTX-0123', 'issue-type-1', 'OPEN');
        $issue2 = new Issue('CTX-0124', 'issue-type-2', 'OPEN');

        $collection = new IssueCollection(
            ['00000000-0000-0000-0000-000000000000' => $issue1, '00000000-0000-0000-0000-000000000001' => $issue2]
        );

        $this->assertEquals($issue1, $collection->getIssueById('00000000-0000-0000-0000-000000000000'));
        $this->assertEquals($issue2, $collection->getIssueById('00000000-0000-0000-0000-000000000001'));
        $this->assertNull($collection->getIssueById('99999999-9999-9999-9999-999999999999'));
    }

    public function test_count()
    {
        $issue1 = new Issue('00000000-0000-0000-0000-000000000000', 'issue-type-1', 'OPEN');
        $issue2 = new Issue('00000000-0000-0000-0000-000000000001', 'issue-type-2', 'OPEN');
        $issue3 = new Issue('00000000-0000-0000-0000-000000000002', 'issue-type-3', 'OPEN');
        $issue4 = new Issue('00000000-0000-0000-0000-000000000003', 'issue-type-4', 'OPEN');
        // Duplicate entries are not counted/added
        $issue5 = new Issue('00000000-0000-0000-0000-000000000003', 'issue-type-4', 'OPEN');
        $issue6 = new Issue('00000000-0000-0000-0000-000000000003', 'issue-type-4', 'OPEN');

        $collection = new IssueCollection([
            $issue1->getKey() => $issue1,
            $issue2->getKey() => $issue2,
            $issue3->getKey() => $issue3,
            $issue4->getKey() => $issue4,
            $issue5->getKey() => $issue5,
            $issue6->getKey() => $issue6
        ]);

        $this->assertCount(4, $collection);
    }
}
