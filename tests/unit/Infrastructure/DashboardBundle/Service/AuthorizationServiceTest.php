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
use Surfnet\ServiceProviderDashboard\Application\Service\ServiceService;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\AuthorizationService;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Authentication\Token\SamlToken;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AuthorizationServiceTest extends MockeryTestCase
{
    /** @var ServiceService|m\MockInterface */
    private $serviceService;

    /** @var Session|m\MockInterface */
    private $session;

    /** @var TokenStorageInterface|m\MockInterface */
    private $tokenStorage;

    /** @var AuthorizationService */
    private $service;

    public function setUp()
    {
        $this->serviceService = m::mock(ServiceService::class);
        $this->session = m::mock(Session::class);
        $this->tokenStorage = m::mock(TokenStorageInterface::class);

        $this->service = new AuthorizationService(
            $this->serviceService,
            $this->session,
            $this->tokenStorage
        );
    }

    /**
     * @group Service
     */
    public function test_service_writes_selected_service_to_session()
    {
        $this->session->shouldReceive('set')
            ->with('selected_service_id', 1);

        $this->service->setSelectedServiceId(1);
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
}
