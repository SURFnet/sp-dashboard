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

use PHPUnit_Framework_TestCase as TestCase;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Secret;

class SecretTest extends TestCase
{
    public function test_secret_generation()
    {
        $secret = new Secret(20);

        $this->assertEquals(20, strlen($secret->getSecret()));

        // The charlist should match characters in Secret::$requiredChars
        $this->assertNotFalse(strpbrk($secret->getSecret(), '~!@#$%^&*_+='));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage The secret length should be a value greater then 7
     */
    public function test_secret_generation_with_length_of_0_should_fail()
    {
        $secret = new Secret(0);
    }


    /**
     * @expectedException \Exception
     * @expectedExceptionMessage The secret length should be a value greater then 7
     */
    public function test_secret_generation_with_length_of_7_should_fail()
    {
        $secret = new Secret(7);
    }

    public function test_secret_generation_with_length_of_8_should_succeed()
    {
        $secret = new Secret(8);

        // The charlist should match characters in Secret::$requiredChars
        $this->assertNotFalse(strpbrk($secret->getSecret(), '~!@#$%^&*_+='));

        $this->assertInstanceOf(Secret::class, $secret);
    }
}
