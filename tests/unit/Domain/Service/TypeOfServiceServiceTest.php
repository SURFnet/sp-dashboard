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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Domain\DashboardBundle\Service;

use PHPUnit\Framework\TestCase;
use Surfnet\ServiceProviderDashboard\Domain\Service\TypeOfServiceService;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\TypeOfService;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\TypeOfServiceCollection;

class TypeOfServiceServiceTest extends TestCase
{
    public function testApplyHiddenTypesWithNoPristineTypes(): void
    {
        $service = new TypeOfServiceService();

        $type1 = new TypeOfService('type1', '', false);
        $type2 = new TypeOfService('type2', '', true);
        $types = new TypeOfServiceCollection();
        $types->add($type1);
        $types->add($type2);

        $result = $service->mergeCollections($types, null);

        $this->assertCount(1, $result->getArray());
        $this->assertSame('type1', $result->getArray()[0]->typeEn);
    }

    public function testApplyHiddenTypesWithPristineTypes(): void
    {
        $service = new TypeOfServiceService();

        $type1 = new TypeOfService('type1', '', false);
        $types = new TypeOfServiceCollection();
        $types->add($type1);

        $pristineType1 = new TypeOfService('type2', '', true);
        $pristineTypes = new TypeOfServiceCollection();
        $pristineTypes->add($pristineType1);

        $result = $service->mergeCollections($types, $pristineTypes);

        $this->assertCount(2, $result->getArray());
        $this->assertSame('type1', $result->getArray()[0]->typeEn);
        $this->assertSame('type2', $result->getArray()[1]->typeEn);
    }

    public function testNoDuplicatesAddedBasedOnTypeEn(): void
    {
        $service = new TypeOfServiceService();

        $type1 = new TypeOfService('type1', '', false);
        $type2 = new TypeOfService('type1', '', true); // Duplicate typeEn
        $types = new TypeOfServiceCollection();
        $types->add($type1);
        $types->add($type2);

        $result = $service->mergeCollections($types, null);

        $this->assertCount(1, $result->getArray());
        $this->assertSame('type1', $result->getArray()[0]->typeEn);
    }

    public function testNoDuplicatesAddedBasedOnTypeEnForPristineTypes(): void
    {
        $service = new TypeOfServiceService();

        $type1 = new TypeOfService('type1', '', false);
        $types = new TypeOfServiceCollection();
        $types->add($type1);

        $pristineType1 = new TypeOfService('type2', '', true);
        $pristineType2 = new TypeOfService('type2', '', true); // Duplicate typeEn
        $pristineTypes = new TypeOfServiceCollection();
        $pristineTypes->add($pristineType1);
        $pristineTypes->add($pristineType2);

        $result = $service->mergeCollections($types, $pristineTypes);

        $this->assertCount(2, $result->getArray());
        $this->assertSame('type1', $result->getArray()[0]->typeEn);
        $this->assertSame('type2', $result->getArray()[1]->typeEn);
    }
}