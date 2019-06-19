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

use PHPUnit_Framework_TestCase as TestCase;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Issue;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\IssueCollection;

class IssueCollectionTest extends TestCase
{
    public function test_get_issue_by_key()
    {
        $issue1 = new Issue('00000000-0000-0000-0000-000000000000', 'issue-type-1');
        $issue2 = new Issue('00000000-0000-0000-0000-000000000001', 'issue-type-2');

        $collection = new IssueCollection([$issue1, $issue2]);

        $this->assertEquals($issue1, $collection->getIssueByKey('00000000-0000-0000-0000-000000000000'));
        $this->assertEquals($issue2, $collection->getIssueByKey('00000000-0000-0000-0000-000000000001'));
        $this->assertNull($collection->getIssueByKey('99999999-9999-9999-9999-999999999999'));
    }

    public function test_count()
    {
        $issue1 = new Issue('00000000-0000-0000-0000-000000000000', 'issue-type-1');
        $issue2 = new Issue('00000000-0000-0000-0000-000000000001', 'issue-type-2');
        $issue3 = new Issue('00000000-0000-0000-0000-000000000002', 'issue-type-3');
        $issue4 = new Issue('00000000-0000-0000-0000-000000000003', 'issue-type-4');
        // Duplicate entries are not counted/added
        $issue5 = new Issue('00000000-0000-0000-0000-000000000003', 'issue-type-4');
        $issue6 = new Issue('00000000-0000-0000-0000-000000000003', 'issue-type-4');

        $collection = new IssueCollection([$issue1, $issue2, $issue3, $issue4, $issue5, $issue6]);

        $this->assertCount(4, $collection);
    }
}
