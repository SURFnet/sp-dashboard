<?php

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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Infrastructure\DashboardBundle\Service;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Surfnet\ServiceProviderDashboard\Application\Service\ServiceService;
use Surfnet\ServiceProviderDashboard\Application\ViewObject\Manage\Config;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Service\OidcCreateEntityEnabledMarshaller;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Exception\ManageConfigNotFoundException;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\AuthorizationService;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Authentication\Token\SamlToken;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AuthorizationServiceTest extends MockeryTestCase
{
    /** @var ServiceService|MockInterface */
    private $serviceService;

    /** @var Session|MockInterface */
    private $session;

    /** @var TokenStorageInterface|MockInterface */
    private $tokenStorage;

    /** @var AuthorizationService */
    private $service;
    /**
     * @var MockInterface&Config
     */
    private $manageConfigTest;
    /**
     * @var MockInterface&Config
     */
    private $manageConfigProd;
    /**
     * @var MockInterface&OidcCreateEntityEnabledMarshaller
     */
    private $marshaller;

    public function setUp()
    {
        $this->serviceService = m::mock(ServiceService::class);
        $this->session = m::mock(Session::class);
        $this->tokenStorage = m::mock(TokenStorageInterface::class);
        $this->manageConfigTest = m::mock(Config::class);
        $this->manageConfigProd = m::mock(Config::class);

        $this->marshaller = m::mock(OidcCreateEntityEnabledMarshaller::class);

        $this->service = new AuthorizationService(
            $this->serviceService,
            $this->session,
            $this->tokenStorage,
            $this->manageConfigTest,
            $this->manageConfigProd,
            $this->marshaller
        );
    }

    /**
     * @group Service
     */
    public function test_service_writes_selected_service_to_session()
    {
        $service = m::mock(Service::class);
        $service->shouldReceive('getId')->andReturn(1);

        $this->tokenStorage->shouldReceive('getToken')
            ->andReturn(new SamlToken(['ROLE_ADMINISTRATOR']));

        $this->serviceService->shouldReceive('getServiceById')->andReturn(
            $service
        );

        $this->serviceService->shouldReceive('getServiceNamesById')
            ->andReturn([
                1 => 'SURFnet',
            ]);

        $this->session->shouldReceive('set')
            ->with('selected_service_id', 1);

        $this->service->changeActiveService(1);
    }

    /**
     * @group Service
     */
    public function test_service_reads_selected_service_from_session()
    {
        $this->tokenStorage->shouldReceive('getToken')
            ->andReturn(new SamlToken(['ROLE_ADMINISTRATOR']));

        $this->serviceService->shouldReceive('getServiceNamesById')
            ->andReturn([
                1 => 'SURFnet',
            ]);

        $this->session->shouldReceive('get')
            ->andReturn(1);

        $this->assertEquals(1, $this->service->getSelectedServiceId());
    }

    /**
     * @group Service
     *
     * @expectedException \RuntimeException
     * @expectedExceptionMessage User is not granted access to service with ID 2
     */
    public function test_service_throws_exception_if_access_to_service_is_not_granted()
    {
        $this->tokenStorage->shouldReceive('getToken')
            ->andReturn(new SamlToken(['ROLE_ADMINISTRATOR']));

        $this->serviceService->shouldReceive('getServiceNamesById')
            ->andReturn([
                1 => 'SURFnet',
            ]);

        $this->session->shouldReceive('get')
            ->andReturn(2);

        $this->service->getSelectedServiceId();
    }

    /**
     * @dataProvider oidcngTestEntries
     * @param bool $expectation
     * @param Service $service
     * @param string $environment
     * @param string $message
     */
    public function test_oidcng_access($expectation, Service $service, $environment, $allowedOnEnv, $message)
    {
        switch ($environment) {
            case 'test':
                $this->manageConfigTest->shouldReceive('getOidcngEnabled->isEnabled')->andReturn($allowedOnEnv);
                break;
            case 'prod':
                $this->manageConfigProd->shouldReceive('getOidcngEnabled->isEnabled')->andReturn($allowedOnEnv);
                break;
        }
        $this->assertEquals($expectation, $this->service->isOidcngAllowed($service, $environment), $message);
    }

    public function oidcngTestEntries()
    {
        $service = m::mock(Service::class);
        $service->shouldReceive('isOidcngEnabled')->andReturn(true);

        $serviceDisabled = m::mock(Service::class);
        $serviceDisabled->shouldReceive('isOidcngEnabled')->andReturn(false);

        return [
            [true, $service, 'test', true, 'Both the service and the manage env has oidcng enabled (test)'],
            [true, $service, 'prod', true, 'Both the service and the manage env has oidcng enabled (prod)'],
            [false, $serviceDisabled, 'test', true, 'Service disabled, and the manage env has oidcng enabled (test)'],
            [false, $serviceDisabled, 'prod', true, 'Service disabled, and the manage env has oidcng enabled (prod)'],
            [false, $service, 'test', false, 'Service enabled, manage env disabled (test)'],
            [false, $service, 'prod', false, 'Service enabled, manage env disabled (prod)'],
            [false, $serviceDisabled, 'test', false, 'Service disabled, manage env has oidcng disabled (test)'],
            [false, $serviceDisabled, 'prod', false, 'Service disabled, manage env has oidcng disabled (prod)'],
        ];
    }

    public function test_oidcng_access_invalid_env()
    {
        $this->expectException(ManageConfigNotFoundException::class);
        $this->expectExceptionMessage('The manage configuration for environment "mumbojumbo" can not be found.');
        $this->service->isOidcngAllowed(m::mock(Service::class), 'mumbojumbo');
    }

    public function test_oidc_create_entity_allowed()
    {
        $this->marshaller
            ->shouldReceive('allowed')
            ->once()
            ->andReturn(false);
        $this->assertFalse($this->service->isOidcCreateEntityAllowed());

        $this->marshaller
            ->shouldReceive('allowed')
            ->once()
            ->andReturn(true);

        $this->assertTrue($this->service->isOidcCreateEntityAllowed());
    }
}
