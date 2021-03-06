<?php

/**
 * Copyright 2018 SURFnet B.V.
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
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Secret;

class SecretTest extends TestCase
{
    public function test_secret_generation()
    {
        $secret = new Secret(20);

        $this->assertEquals(20, strlen($secret->getSecret()));

        $this->assertNotFalse(strpbrk($secret->getSecret(), '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'));
    }

    public function test_secret_generation_with_length_of_0_should_fail()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The secret length should be a value greater or equal to 20');
        new Secret(0);
    }
    public function test_secret_generation_with_length_of_19_should_fail()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The secret length should be a value greater or equal to 20');
        new Secret(19);
    }

    public function test_secret_generation_with_length_of_20_should_succeed()
    {
        $secret = new Secret(20);

        $this->assertInstanceOf(Secret::class, $secret);
    }
}
