<?php
declare(strict_types = 1);
/**
 * Copyright 2024 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Application\ViewObject;

use PHPUnit\Framework\TestCase;
use Surfnet\ServiceProviderDashboard\Application\ViewObject\EntityConnection;
use Surfnet\ServiceProviderDashboard\Domain\Entity\IdentityProvider;

class EntityConnectionTest extends TestCase
{
    public function test_correct_lists_are_created()
    {
        $availableTest = [
            'mock-idp' => new IdentityProvider('123123', 'mock-idp', 'Mujina', 'Muhjina'),
            'https://eduid.nl' => new IdentityProvider('4363456', 'eduid', 'EduId', 'Eduid'),
            'https://test.example.nl' => new IdentityProvider('86524365', 'Test', 'Test', 'Test'),
        ];
        $availableOther = [
            'https://foobar.org' => new IdentityProvider('43688434', 'foobar', 'foobar', 'foobar'),
        ];
        $connected = [
            'https://eduid.nl' => new IdentityProvider('4363456', 'eduid', 'EduId', 'Eduid'),
            'https://foobar.org' => new IdentityProvider('43688434', 'foobar', 'foobar', 'foobar'),
        ];

        $connection = new EntityConnection(
            'SP Exceptionale',
            'Vendor',
            'https://entityId',
            $availableTest,
            $availableOther,
            $connected
        );

        $this->assertEquals($availableTest, $connection->listAvailableTestIdps());
        $expectedConnections = [
            'mock-idp' => false,
            'https://eduid.nl' => true,
            'https://test.example.nl' => false,
        ];

        $this->assertEquals($expectedConnections, $connection->listConnected());
    }

    public function test_allowall_results_in_all_selected()
    {
        $availableTest = [
            'mock-idp' => new IdentityProvider('123123', 'mock-idp', 'Mujina', 'Muhjina'),
            'https://eduid.nl' => new IdentityProvider('4363456', 'eduid', 'EduId', 'Eduid'),
            'https://test.example.nl' => new IdentityProvider('86524365', 'Test', 'Test', 'Test'),
        ];
        $availableOther = [
            'https://foobar.org' => new IdentityProvider('43688434', 'foobar', 'foobar', 'foobar'),
        ];
        $connected = [
            'mock-idp' => new IdentityProvider('123123', 'mock-idp', 'Mujina', 'Muhjina'),
            'https://eduid.nl' => new IdentityProvider('4363456', 'eduid', 'EduId', 'Eduid'),
            'https://test.example.nl' => new IdentityProvider('86524365', 'Test', 'Test', 'Test'),
            'https://foobar.org' => new IdentityProvider('43688434', 'foobar', 'foobar', 'foobar'),
        ];

        $connection = new EntityConnection(
            'SP Exceptionale',
            'https://entityId',
            'Vendor',
            $availableTest,
            $availableOther,
            $connected
        );

        $this->assertEquals($availableTest, $connection->listAvailableTestIdps());
        $expectedConnections = [
            'mock-idp' => true,
            'https://eduid.nl' => true,
            'https://test.example.nl' => true,
            // 'https://foobar.org' => true, // The other connected IdP is not listed explicitly, as we do not show
            // the other connected IdPs on the list page (we do on the CSV export)
        ];

        $this->assertEquals($expectedConnections, $connection->listConnected());
    }
}
