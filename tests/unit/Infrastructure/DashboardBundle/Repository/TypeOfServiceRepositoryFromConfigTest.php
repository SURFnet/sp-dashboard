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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Infrastructure\DashboardBundle\Repository;

use PHPUnit\Framework\TestCase;
use Surfnet\ServiceProviderDashboard\Application\Exception\RuntimeException;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\TypeOfService;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Repository\TypeOfServiceRepositoryFromConfig;

class TypeOfServiceRepositoryFromConfigTest extends TestCase
{
    public function test_it_loads_types_of_service()
    {
        $typesRepo = new TypeOfServiceRepositoryFromConfig(__DIR__ . '/Fixtures/type_of_service.json');
        $results = $typesRepo->getTypesOfServiceChoices();
        self::assertIsArray($results);
        self::assertCount(14, $results);
        self::assertContainsOnlyInstancesOf(TypeOfService::class, $results);
    }
    public function test_it_rejects_non_existing_file()
    {
        self::expectException(RuntimeException::class);
        self::expectExceptionMessageMatches('/Please review the file location of the type of services json blob/');
        $typesRepo = new TypeOfServiceRepositoryFromConfig('/Fixtures/shes_not_there.json');
        $typesRepo->getTypesOfServiceChoices();
    }
    public function test_it_rejects_faulty_json()
    {
        self::expectException(RuntimeException::class);
        self::expectExceptionMessage('The json can not be parsed into an array of service types');
        $typesRepo = new TypeOfServiceRepositoryFromConfig(__DIR__ . '/Fixtures/type_of_service_corrupt_json.json');
        $typesRepo->getTypesOfServiceChoices();
    }
}
