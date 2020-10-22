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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Infrastructure\DashboardBundle\Form\Entity;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Surfnet\ServiceProviderDashboard\Application\ViewObject\Manage\Config;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Entity\ProtocolChoiceFactory;

class ProtocolChoiceFactoryTest extends MockeryTestCase
{
    /** @var ProtocolChoiceFactory  */
    private $protocolChoiceFactory;
    /** @var m\MockInterface&Config */
    private $manageTestConfig;
    /** @var m\MockInterface&Config */
    private $manageProdConfig;
    /** @var m\MockInterface&Service */
    private $service;

    /**
     * @param string $testDescription
     * @param array $expectation
     * @param bool $testOidcngEnabled
     * @param bool $oidcngEnabledForService
     *
     * @dataProvider provideTestVariations
     */
    public function test_variations_test(
        $testDescription,
        $expectation,
        $testOidcngEnabled,
        $oidcngEnabledForService
    ) {
        $this->manageTestConfig
            ->shouldReceive('getOidcngEnabled->isEnabled')
            ->once()
            ->andReturn($testOidcngEnabled);

        $this->service
            ->shouldReceive('isOidcngEnabled')
            ->andReturn($oidcngEnabledForService);

        $testOptions = $this->protocolChoiceFactory->buildOptions(Constants::ENVIRONMENT_TEST);
        $this->assertEquals($expectation, array_values($testOptions), $testDescription);
    }

    /**
     * @param string $testDescription
     * @param array $expectation
     * @param bool $productionOidcngEnabled
     * @param bool $oidcngEnabledForService
     *
     * @dataProvider provideTestVariations Note that the test generator is used, as for now behaviour is similar
     *                                     between prod and test
     */
    public function test_variations_production(
        $testDescription,
        $expectation,
        $productionOidcngEnabled,
        $oidcngEnabledForService
    ) {
        $this->manageProdConfig
            ->shouldReceive('getOidcngEnabled->isEnabled')
            ->once()
            ->andReturn($productionOidcngEnabled);

        $this->service
            ->shouldReceive('isOidcngEnabled')
            ->andReturn($oidcngEnabledForService);

        $testOptions = $this->protocolChoiceFactory->buildOptions(Constants::ENVIRONMENT_PRODUCTION);
        $this->assertEquals($expectation, array_values($testOptions), $testDescription);
    }

    public function provideTestVariations()
    {
        return [
            [
                'All systems go, all options are set to true, so all options are displayed',
                [
                    'saml20',
                    'oidcng',
                    'oidcng_rs',
                ],
                true,
                true,
            ],
            [
                'OIDCng is disabled, Only SAML should be visible',
                [
                    'saml20',
                ],
                false,
                true,
            ],
            [
                'OIDCng is disabled for the service, Only SAML should be visible',
                [
                    'saml20',
                ],
                true,
                false,
            ],
        ];
    }

    protected function setUp()
    {
        $this->manageTestConfig = m::mock(Config::class);
        $this->manageProdConfig = m::mock(Config::class);
        $this->protocolChoiceFactory = new ProtocolChoiceFactory(
            $this->manageTestConfig,
            $this->manageProdConfig
        );
        $this->service = m::mock(Service::class);
        $this->protocolChoiceFactory->setService($this->service);
    }
}
