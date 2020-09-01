<?php

/**
 * Copyright 2020 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Domain\Entity\Entity;

use PHPUnit\Framework\TestCase;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Logo;

class LogoTest extends TestCase
{
    /**
     * @dataProvider provideLogoTestData
     */
    public function test_it_can_merge_data(Logo $logo, ?Logo $newData, array $expectations)
    {
        $logo->merge($newData);

        self::assertEquals($expectations['url'], $logo->getUrl());
        self::assertEquals($expectations['width'], $logo->getWidth());
        self::assertEquals($expectations['height'], $logo->getHeight());
    }

    public function provideLogoTestData()
    {
        yield [
            new Logo('https://www.url1.com/img/logo.png', 30, 30),
            new Logo('https://www.url2.com/img/logo.png', 30, 34),
            ['url' => 'https://www.url2.com/img/logo.png', 'width' => 30, 'height' => 34]
        ];
        yield [
            new Logo('https://www.url1.com/img/logo.png', 30, 30),
            null,
            ['url' => null, 'width' => null, 'height' => null]
        ];
        yield [
            new Logo('https://www.url1.com/img/logo.png', 30, 30),
            new Logo('https://www.url2.com/img/logo.png', null, null),
            ['url' => 'https://www.url2.com/img/logo.png', 'width' => null, 'height' => null]
        ];
        yield [
            new Logo('https://www.url1.com/img/logo.png', 30, 30),
            new Logo(null, null, null),
            ['url' => null, 'width' => null, 'height' => null]
        ];
    }
}
