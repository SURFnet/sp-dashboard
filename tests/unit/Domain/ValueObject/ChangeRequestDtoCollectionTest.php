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

use Exception;
use PHPUnit\Framework\TestCase;
use Surfnet\ServiceProviderDashboard\Application\Dto\ChangeRequestDto;
use Surfnet\ServiceProviderDashboard\Application\Dto\ChangeRequestDtoCollection;

class ChangeRequestDtoCollectionTest extends TestCase
{
    public function test_it_is_created()
    {
        $changes[] = [
            'id' => 1,
            'note' => 'a cracked note',
            'created' => '2022-09-21 15:00:00',
            'pathUpdates' => [
                'metaDataFields.description:nl' => 'description nl',
                'metaDataFields.description:en' => 'description en'
            ]
        ];
        $changes[] = [
            'id' => 1,
            'note' => 'another cracked note',
            'created' => '2022-09-21 16:00:00',
            'pathUpdates' => [
                'metaDataFields.name:nl' => 'change nl 2',
                'metaDataFields.name:en' => 'change en 2'
            ]
        ];

        $collection = new ChangeRequestDtoCollection($changes);

        $this->assertIsArray($collection->getChangeRequests());
        $this->assertEquals(2, $collection->count());
        $this->assertCount(2, $collection->getChangeRequests());
    }
}

