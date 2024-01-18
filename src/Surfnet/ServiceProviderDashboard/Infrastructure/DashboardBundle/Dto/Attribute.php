<?php

declare(strict_types = 1);

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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Dto;

class Attribute implements AttributeInterface
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var mixed[]
     */
    public $excludeOnEntityType = [];

    /**
     * @var mixed[]
     */
    public $translations = [];

    /**
     * @var mixed[]
     */
    public $urns = [];

    private function __construct(
        string $id,
        array $excludeOnEntityType,
        array $translations,
        array $urns
    ) {
        $this->id = $id;
        $this->excludeOnEntityType = $excludeOnEntityType;
        $this->translations = $translations;
        $this->urns = $urns;
    }

    public static function fromAttribute(array $attribute): ?Attribute
    {
        return new self(
            $attribute['id'],
            self::excludeOnEntityType($attribute),
            self::translations($attribute['translations']),
            $attribute['urns']
        );
    }

    private static function translations(array $languages): array
    {
        $translations = [];
        foreach ($languages as $language => $values) {
            $translations[$language] = AttributeTypeInformation::fromLanguage($values, $language);
        }
        return $translations;
    }

    private static function excludeOnEntityType(array $attribute): ?array
    {
        $exclude = [];
        if (array_key_exists('excludeOnEntityType', $attribute)) {
            $exclude = $attribute['excludeOnEntityType'];
        }
        return $exclude;
    }
}
