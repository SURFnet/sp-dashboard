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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Application\Parser;

use PHPUnit\Framework\TestCase;
use Surfnet\ServiceProviderDashboard\Application\Parser\OidcngSpdClientIdParser;

class OidcngSpdClientIdParserTest extends TestCase
{
    /**
     * @param $url
     * @param $expectation
     * @param $message
     *
     * @dataProvider entityIdGenerator
     */
    public function test_expected_transformation($url, $expectation, $message)
    {
        $this->assertEquals($expectation, OidcngSpdClientIdParser::parse($url), $message);
    }

    public static function entityIdGenerator()
    {
        return [
            ['www.google.com', 'https://www.google.com', 'parses https'],
            ['www.google.com/entityId', 'https://www.google.com/entityId', 'parses https with path'],
            ['https://foobar.example.com', 'https://https://foobar.example.com', 'simply prepends https://'],
            ['foobar', 'https://foobar', 'Does not produce a valid URL/URN perse']
        ];
    }
}
