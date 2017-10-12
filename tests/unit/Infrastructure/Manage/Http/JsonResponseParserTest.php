<?php

/**
 * Copyright 2017 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Infrastructure\Manage\Http;

use PHPUnit_Framework_TestCase as TestCase;
use stdClass;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Exception\InvalidJsonException;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Http\JsonResponseParser;

class JsonResponseParserTest extends TestCase
{
    /**
     * @dataProvider notString
     */
    public function test_json_response_to_parse_must_be_a_string($nonString)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('expected to be string');

        JsonResponseParser::parse($nonString);
    }

    public function test_an_exception_is_thrown_if_the_json_is_malformed()
    {
        $this->expectException(InvalidJsonException::class);
        $this->expectExceptionMessage('malformed JSON');

        $malformedJson = '{';

        JsonResponseParser::parse($malformedJson);
    }

    public static function notString()
    {
        return [
            'integer' => [1],
            'float'   => [1.234],
            'true'    => [true],
            'false'   => [false],
            'array'   => [[]],
            'object'  => [new stdClass()],
            'null'    => [null]
        ];
    }
}
