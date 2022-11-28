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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Infrastructure\DashboardBundle\Dto;

use PHPUnit\Framework\TestCase;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Dto\AttributeTypeInformation;

class AttributeTypeInformationTest extends TestCase
{
    public function test_it_is_created()
    {
        $en = [
                'label' => 'emailAddressAttribute',
                'info' => 'Description is placed here'
        ];
        $language = AttributeTypeInformation::fromLanguage($en, 'en');

        $this->assertEquals('emailAddressAttribute', $language->label);
        $this->assertEquals('Description is placed here', $language->info);
    }
}
