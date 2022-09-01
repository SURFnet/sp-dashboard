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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Infrastructure\DashboardBundle\Manage\Dto;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Surfnet\ServiceProviderDashboard\Domain\Entity\EntityDiff;

class EntityDiffTest extends MockeryTestCase
{
    public function test_empty_diff()
    {
        $data = [];
        $compareTo = [];
        $diff = new EntityDiff($data, $compareTo);
        $this->assertEmpty($diff->getDiff());
    }

    public function test_addition()
    {
        $data = ['a' => 'is for apple', 'd' => 'is for drums'];
        $compareTo = ['a' => 'is for apple'];
        $diff = (new EntityDiff($data, $compareTo))->getDiff();
        $this->assertCount(1, $diff);
        $this->assertArrayHasKey('d', $diff);
    }

    public function test_change()
    {
        $data = ['d' => 'is for DRUMS!'];
        $compareTo = ['d' => 'is for drums'];
        $diff = (new EntityDiff($data, $compareTo))->getDiff();
        $this->assertCount(1, $diff);
        $this->assertArrayHasKey('d', $diff);
        $this->assertEquals('is for DRUMS!', $diff['d']);
    }

    public function test_removed()
    {
        $data = [];
        $compareTo = ['d' => 'is for drums'];
        $diff = (new EntityDiff($data, $compareTo))->getDiff();
        $this->assertCount(1, $diff);
        $this->assertArrayHasKey('d', $diff);
        $this->assertNull($diff['d']);
    }

    public function test_recursion()
    {
        $data = ['a' => 'is for apples', 'd' => ['is for drums', 'is for duck']];
        $compareTo = ['a' => 'is for apple', 'd' => ['is for drums', 'is for door']];
        $diff = (new EntityDiff($data, $compareTo))->getDiff();

        $this->assertArrayHasKey('d', $diff);
        $this->assertArrayHasKey('a', $diff);
        $this->assertSame([1 => 'is for duck'], $diff['d']);
    }

    public function test_a_bit_of_everything()
    {
        $data = [
            'a' => 'is for apple',
            'b' => 'is for balloon',
            'c' => 'is for crayons',
        ];

        $compareTo = [
            'b' => 'is for balloon',
            'c' => 'is for crayon',
            'd' => 'is for drums'
        ];

        $diff = (new EntityDiff($data, $compareTo))->getDiff();
        $this->assertCount(3, $diff);
        // added
        $this->assertArrayHasKey('a', $diff);
        $this->assertEquals('is for apple', $diff['a']);
        // changed
        $this->assertArrayHasKey('c', $diff);
        $this->assertEquals('is for crayons', $diff['c']);
        // deleted
        $this->assertArrayHasKey('d', $diff);
        $this->assertNull($diff['d']);
    }
}
