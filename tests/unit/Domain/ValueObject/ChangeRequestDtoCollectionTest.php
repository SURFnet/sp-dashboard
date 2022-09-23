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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Domain\ValueObject;

use PHPUnit\Framework\TestCase;
use Surfnet\ServiceProviderDashboard\Application\Dto\ChangeRequestDtoCollection;
use Webmozart\Assert\InvalidArgumentException;

class ChangeRequestDtoCollectionTest extends TestCase
{
    public function test_it_is_created()
    {
        $changes = [
            [
                'id' => 1,
                'note' => 'a cracked note',
                'created' => '2022-09-21 15:00:00',
                'pathUpdates' => [
                    'metaDataFields.description:nl' => 'description nl',
                    'metaDataFields.description:en' => 'description en'
                ]
            ],
            [
                'id' => 1,
                'note' => 'another cracked note',
                'created' => '2022-09-21 16:00:00',
                'pathUpdates' => [
                    'metaDataFields.name:nl' => 'change nl 2',
                    'metaDataFields.name:en' => 'change en 2'
                ]
            ]
        ];

        $collection = new ChangeRequestDtoCollection($changes);

        $this->assertIsArray($collection->getChangeRequests());
        $this->assertCount(2, $collection->getChangeRequests());
    }

    public function test_it_sorts_on_created_date_time_descending()
    {
        $changes = [
            [
                'id' => 1,
                'created' => '2022-09-21 16:00:00',
                'pathUpdates' => [
                    'metaDataFields.description:nl' => 'description nl',
                ]
            ],
            [
                'id' => 2,
                'created' => '2022-09-21 15:00:00',
                'pathUpdates' => [
                    'metaDataFields.description:nl' => 'description en',
                    ]
            ],
            [
                'id' => 3,
                'created' => '2022-09-21 17:00:00',
                'pathUpdates' => [
                    'metaDataFields.description:nl' => 'description de',
                    ]
            ]
        ];
        $collection = new ChangeRequestDtoCollection($changes);

        $this->assertIsArray($collection->getChangeRequests());
        $this->assertCount(3, $collection->getChangeRequests());
        $this->assertEquals('2022-09-21 17:00:00', ($collection->getChangeRequests()[0])->getCreated()->format('Y-m-d H:i:s'));
        $this->assertEquals('2022-09-21 16:00:00', ($collection->getChangeRequests()[1])->getCreated()->format('Y-m-d H:i:s'));
        $this->assertEquals('2022-09-21 15:00:00', ($collection->getChangeRequests()[2])->getCreated()->format('Y-m-d H:i:s'));
        $this->assertEquals(3, ($collection->getChangeRequests()[0])->getId());
        $this->assertEquals(1, ($collection->getChangeRequests()[1])->getId());
        $this->assertEquals(2, ($collection->getChangeRequests()[2])->getId());
    }

    public function test_it_handles_empty_changes()
    {
        $changes = [];
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No change requests available');
        new ChangeRequestDtoCollection($changes);
    }
}
