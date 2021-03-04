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
use Surfnet\ServiceProviderDashboard\Application\Parser\OidcngClientIdParser;

class OidcngClientIdParserTest extends TestCase
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
        $this->assertEquals($expectation, OidcngClientIdParser::parse($url), $message);
    }

    public static function entityIdGenerator()
    {
        return [
            ['https://www.google.com', 'www.google.com', 'parses https'],
            ['https://www.google.com/entityId', 'www.google.com/entityId', 'parses https with path'],
            ['http://www.google.com', 'www.google.com', 'parses http'],
            ['http://www.google.com/entityId', 'www.google.com/entityId', 'parses https with path'],
            ['http://www.google.com/', 'www.google.com', 'strips trailing slash'],
        ];
    }
}
