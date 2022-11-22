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
use Surfnet\ServiceProviderDashboard\Application\ViewObject\Apis\ApiConfig as Config;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
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

    public function setUp(): void
    {
        $this->serviceService = m::mock(ServiceService::class);
        $this->session = m::mock(Session::class);
        $this->tokenStorage = m::mock(TokenStorageInterface::class);
        $this->manageConfigTest = m::mock(Config::class);
        $this->manageConfigProd = m::mock(Config::class);

        $this->service = new AuthorizationService(
            $this->serviceService,
            $this->session,
            $this->tokenStorage,
            $this->manageConfigTest,
            $this->manageConfigProd
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

        $service = $this->service->changeActiveService(1);

        $this->assertInstanceOf(Service::class, $service);
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

        $this->expectExceptionMessage("User is not granted access to service with ID 2");
        $this->expectException(\RuntimeException::class);
        $this->service->getSelectedServiceId();
    }
}
