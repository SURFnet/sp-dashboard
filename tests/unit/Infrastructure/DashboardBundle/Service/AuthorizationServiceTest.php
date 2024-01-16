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
use Surfnet\SamlBundle\Security\Authentication\Token\SamlToken;
use Surfnet\ServiceProviderDashboard\Application\Service\ServiceService;
use Surfnet\ServiceProviderDashboard\Application\ViewObject\Apis\ApiConfig as Config;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\AuthorizationService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AuthorizationServiceTest extends MockeryTestCase
{
    private ServiceService|MockInterface|m\LegacyMockInterface $serviceService;
    private Session|m\LegacyMockInterface|MockInterface $session;
    private AuthorizationService $service;

    public function setUp(): void
    {
        $this->serviceService = m::mock(ServiceService::class);
        $tokenStorage = m::mock(TokenStorageInterface::class);
        $manageConfigTest = m::mock(Config::class);
        $manageConfigProd = m::mock(Config::class);
        $requestStack = m::mock(RequestStack::class);

        $token = m::mock(SamlToken::class);
        $token->shouldReceive('hasRole')->with('ROLE_ADMINISTRATOR')->andReturnTrue();
        $token->shouldReceive('getRoleNames')->andReturn(['ROLE_ADMINISTRATOR']);
        $tokenStorage->shouldReceive('getToken')
            ->andReturn($token);
        $requestStack->shouldReceive('getSession')->andReturn(m::mock(Session::class));

        $this->service = new AuthorizationService(
            $this->serviceService,
            $requestStack,
            $tokenStorage,
            $manageConfigTest,
            $manageConfigProd
        );

        $this->session = $requestStack->getSession();
    }

    /**
     * @group Service
     */
    public function test_service_writes_selected_service_to_session()
    {
        $service = m::mock(Service::class);
        $service->shouldReceive('getId')->andReturn(1);


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
