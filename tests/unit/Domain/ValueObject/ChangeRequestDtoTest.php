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
use Surfnet\ServiceProviderDashboard\Application\Dto\ChangeRequestDto;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidDateTimeException;
use Webmozart\Assert\InvalidArgumentException;

class ChangeRequestDtoTest extends TestCase
{
    public function test_it_is_created()
    {
        $changes = [
            'id' => 1,
            'note' => 'a cracked note',
            'created' => '2022-09-21 15:00:00',
            'pathUpdates' => [
                'metaDataFields.description:nl' => 'description nl',
                'metaDataFields.description:en' => 'description en'
            ]
        ];

        $changeRequest = ChangeRequestDto::fromChangeRequest($changes);

        $this->assertEquals(1, $changeRequest->getId());
        $this->assertEquals('a cracked note', $changeRequest->getNote());
        $this->assertEquals('2022-09-21 15:00:00', $changeRequest->getCreated()->format('Y-m-d H:i:s'));
        $this->assertIsArray($changeRequest->getPathUpdates());
        $this->assertEquals(2, count($changeRequest->getPathUpdates()));
    }

    public function test_it_throw_exception_on_invalid_date_time()
    {
        $changes = [
            'id' => 1,
            'note' => 'a cracked note',
            'created' => 'not a datetime',
            'pathUpdates' => [
                'metaDataFields.description:nl' => 'description nl',
                'metaDataFields.description:en' => 'description en'
            ]
        ];

        $this->expectException(InvalidDateTimeException::class);
        ChangeRequestDto::fromChangeRequest($changes);
    }

    public function test_it_allows_an_null_note()
    {
        $changes = [
            'id' => 1,
            'note' => null,
            'created' => '2022-09-21 15:00:00',
            'pathUpdates' => [
                'metaDataFields.description:nl' => 'description nl',
                'metaDataFields.description:en' => 'description en'
            ]
        ];
        $changeRequest = ChangeRequestDto::fromChangeRequest($changes);
        $this->assertEquals(null, $changeRequest->getNote());
    }

    public function test_it_allows_a_missing_note()
    {
        $changes = [
            'id' => 1,
            'created' => '2022-09-21 15:00:00',
            'pathUpdates' => [
                'metaDataFields.description:nl' => 'description nl',
                'metaDataFields.description:en' => 'description en'
            ]
        ];
        $changeRequest = ChangeRequestDto::fromChangeRequest($changes);
        $this->assertEquals('', $changeRequest->getNote());
    }

    public function test_it_handles_invalid_input()
    {
        $changes = [];
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No id specified');
        ChangeRequestDto::fromChangeRequest($changes);
    }

    public function test_it_throws_exception_on_missing_create_date_time()
    {
        $changes = [
            'id' => 1,
        ];
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No create datetime specified');
        ChangeRequestDto::fromChangeRequest($changes);
    }

    public function test_it_throws_exception_on_missing_path_updates()
    {
        $changes = [
            'id' => 1,
            'created' => '2022-09-21 15:00:00',
        ];
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No pathUpdates specified');
        ChangeRequestDto::fromChangeRequest($changes);
    }
}
