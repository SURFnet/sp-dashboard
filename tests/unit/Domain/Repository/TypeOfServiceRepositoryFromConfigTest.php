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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Domain\DashboardBundle\Repository;

use PHPUnit\Framework\TestCase;
use Surfnet\ServiceProviderDashboard\Application\Exception\RuntimeException;
use Surfnet\ServiceProviderDashboard\Domain\Exception\TypeOfServiceException;
use Surfnet\ServiceProviderDashboard\Domain\Repository\TypeOfServiceRepositoryFromConfig;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\TypeOfService;

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
        self::expectException(TypeOfServiceException::class);
        self::expectExceptionMessageMatches('/Please review the file location of the type of services json blob/');
        $typesRepo = new TypeOfServiceRepositoryFromConfig('/Fixtures/shes_not_there.json');
        $typesRepo->getTypesOfServiceChoices();
    }
    public function test_it_rejects_faulty_json()
    {
        self::expectException(TypeOfServiceException::class);
        self::expectExceptionMessage('The json can not be parsed into an array of service types');
        $typesRepo = new TypeOfServiceRepositoryFromConfig(__DIR__ . '/Fixtures/type_of_service_corrupt_json.json');
        $typesRepo->getTypesOfServiceChoices();
    }

    /**
     * @dataProvider proivdeTypeOfServiceExpectations
     */
    public function test_find_by_english_type_of_service(
        string $searchText,
        null|TypeOfService $expected,
    ): void {
        $typesRepo = new TypeOfServiceRepositoryFromConfig();
        $result = $typesRepo->findByEnglishTypeOfService($searchText);
        self::assertEquals($expected, $result);

        if ($expected !== null) {
            self::assertEquals($expected->typeEn, $result->typeEn);
            self::assertEquals($expected->typeNl, $result->typeNl);
        }
    }

    public function proivdeTypeOfServiceExpectations()
    {
        return [
            'existing' => ['Tooling', new TypeOfService('Tooling', 'Tooling')],
            'existing 2' => ['Management of education/research', new TypeOfService('Management of education/research', 'Organisatie van onderwijs/onderzoek')],
            'existing 3' => ['Research', new TypeOfService('Research', 'Onderzoek')],
            'is case sensitive' => ['tooling', null],
            'only finds by english text' => ['Onderzoek', null],
        ];
    }

}
