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
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Http\ResourcePathFormatter;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Exception\InvalidArgumentException;

class ResourcePathFormatterTest extends TestCase
{
    /**
     * @dataProvider notString
     * @param $nonString
     */
    public function test_resource_path_formats_can_only_be_strings($nonString)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('expected to be string');

        ResourcePathFormatter::format($nonString, []);
    }

    public function test_resource_parameters_are_formatted_correctly()
    {
        $resourcePathFormat = 'resource/%s/%d';
        $parameters = ['id', 2];

        $expectedFormattedResourcePath = 'resource/id/2';
        $actualFormattedResourcePath = ResourcePathFormatter::format($resourcePathFormat, $parameters);

        $this->assertSame($expectedFormattedResourcePath, $actualFormattedResourcePath);
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
