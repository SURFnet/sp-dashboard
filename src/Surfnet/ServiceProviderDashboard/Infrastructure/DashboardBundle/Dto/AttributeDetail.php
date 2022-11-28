<?php

declare(strict_types=1);

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

class AttributeDetail
{
    public $languages = [];

    private function __construct(array $languages)
    {
        $this->languages = $languages;
    }

    public static function from(array $languages): AttributeDetail
    {
        $output = [];
        foreach ($languages as $language => $values) {
            $output[$language] = AttributeDetailLanguage::from($values);
        }
        return new self(
            $output
        );
    }
}
