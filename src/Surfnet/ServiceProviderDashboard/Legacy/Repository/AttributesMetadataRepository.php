<?php

declare(strict_types = 1);

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

namespace Surfnet\ServiceProviderDashboard\Legacy\Repository;

use stdClass;
use Surfnet\ServiceProviderDashboard\Domain\Repository\AttributesMetadataRepository as AttributesMetadataRepositoryInterface;

class AttributesMetadataRepository implements AttributesMetadataRepositoryInterface
{
    /**
     * @param string $rootDir
     */
    public function __construct(private $rootDir)
    {
    }

    /**
     * @return stdClass
     */
    public function findAll()
    {
        return json_decode(
            file_get_contents($this->rootDir . '/metadata/attributes.json')
        );
    }

    /**
     * @return stdClass
     */
    public function findAllPrivacyQuestionsAttributes()
    {
        return json_decode(
            file_get_contents($this->rootDir . '/metadata/privacy_questions.json')
        );
    }

    /**
     * @return stdClass
     */
    public function findAllSpDashboardAttributes()
    {
        return json_decode(
            file_get_contents($this->rootDir . '/metadata/sp_dashboard.json')
        );
    }

    /**
     * @return string[]
     */
    public function findAllAttributeUrns(): array
    {
        $names = [];
        foreach ($this->findAll() as $definition) {
            $names[] = reset($definition->urns);
        }
        return $names;
    }
}
