<?php

/**
 * Copyright 2019 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Infrastructure\DashboardBundle\Manage\Dto;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Protocol;

class ProtocolTest extends MockeryTestCase
{
    /**
     * @dataProvider manageData
     * @param array $manageData
     * @param int $manageProtocol
     * @param string $expectation
     */
    public function test_protocol_determination(array $manageData, $manageProtocol, $expectation)
    {
        $protocol = Protocol::fromApiResponse($manageData, $manageProtocol);
        self::assertEquals($expectation, $protocol->getProtocol());
    }

    public static function manageData()
    {
        return [
            [['data' => ['oidcClient-not-set' => null]], Protocol::SAML20_SP, 'saml20'],
            [['data' => ['oidcClient-not-set' => 1]], Protocol::OIDC10_RP, 'oidcng'],
        ];
    }
}
