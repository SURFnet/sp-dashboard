<?php

declare(strict_types = 1);

/**
 * Copyright 2025 SURFnet B.V.
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
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\TypeOfService;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\TypeOfServiceCollection;

class TypeOfServiceCollectionTest extends TestCase
{

    public function testFiltersHidden()
    {
        $typeOfServiceCollection = new TypeOfServiceCollection();
        $typeOfServiceCollection->add(new TypeOfService('#1', '', true));
        $typeOfServiceCollection->add(new TypeOfService('#2', '', false));
        $typeOfServiceCollection->add(new TypeOfService('#3', '', true));
        $typeOfServiceCollection->add(new TypeOfService('#4', '', false));

        $expectedCollection = new TypeOfServiceCollection();
        $expectedCollection->add(new TypeOfService('#2', '', false));
        $expectedCollection->add(new TypeOfService('#4', '', false));

        $this->assertEquals($expectedCollection, $typeOfServiceCollection->filterHidden());
    }

    public function testEmptyCollection()
    {
        $typeOfServiceCollection = new TypeOfServiceCollection();
        $expectedCollection = new TypeOfServiceCollection();

        $this->assertEquals($expectedCollection, $typeOfServiceCollection->filterHidden());
    }

    public function testEmptyResult()
    {
        $typeOfServiceCollection = new TypeOfServiceCollection();
        $typeOfServiceCollection->add(new TypeOfService('#1', '', true));

        $expectedCollection = new TypeOfServiceCollection();

        $this->assertEquals($expectedCollection, $typeOfServiceCollection->filterHidden());
    }

}
