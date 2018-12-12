<?php

/**
 * Copyright 2018 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Application\Service;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\Mock;
use Surfnet\ServiceProviderDashboard\Application\Provider\EntityQueryRepositoryProvider;
use Surfnet\ServiceProviderDashboard\Application\Service\EntityService;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Repository\EntityRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\QueryClient as ManageQueryClient;
use Symfony\Component\Routing\RouterInterface;

class EntityServiceTest extends MockeryTestCase
{
    /**
     * @var Mock|EntityRepository
     */
    private $repository;

    /**
     * @var Mock|ManageQueryClient
     */
    private $manageTest;

    /**
     * @var Mock|ManageQueryClient
     */
    private $manageProd;

    /**
     * @var Mock|RouterInterface
     */
    private $router;

    /**
     * @var EntityService
     */
    private $service;

    public function setUp()
    {
        $this->repository = m::mock(EntityRepository::class);
        $this->manageTest = m::mock(ManageQueryClient::class);
        $this->manageProd = m::mock(ManageQueryClient::class);

        $provider = new EntityQueryRepositoryProvider($this->repository, $this->manageTest, $this->manageProd);

        $this->router = m::mock(RouterInterface::class);
        $this->service = new EntityService($provider, $this->router, 'playgroundUriTest', 'playgroundUriProd');
    }

    public function test_it_can_search_manage_test_by_manage_id()
    {
        $this->manageTest
            ->shouldReceive('findByManageId')
            ->with('a8e7cffd-0409-45c7-a37a-000000000000')
            ->andReturn([]);

        $entity = $this->service->getManageEntityById('a8e7cffd-0409-45c7-a37a-000000000000');
        $this->assertNotNull($entity);
    }

    public function test_it_can_search_manage_prod_by_manage_id()
    {
        $this->manageProd
            ->shouldReceive('findByManageId')
            ->with('a8e7cffd-0409-45c7-a37a-000000000000')
            ->andReturn([]);

        $entity = $this->service->getManageEntityById('a8e7cffd-0409-45c7-a37a-000000000000', 'production');
        $this->assertNotNull($entity);
    }

    /**
     * @expectedException \Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException
     * @expectedExceptionMessage Unsupported environment "staging" requested.
     */
    public function test_it_rejects_invalid_evnironment_when_searching_manage_entity()
    {
        $this->service->getManageEntityById('a8e7cffd-0409-45c7-a37a-000000000000', 'staging');
    }

    public function test_get_entity_list_for_service()
    {
        $service = m::mock(Service::class);

        $serviceId = 1;
        $teamName = 'team-x';

        $service
            ->shouldReceive('getId')
            ->andReturn($serviceId)
            ->once();

        $service
            ->shouldReceive('getTeamName')
            ->andReturn($teamName)
            ->twice();

        $this->repository
            ->shouldReceive('findByServiceId')
            ->with($serviceId)
            ->andReturn([]);

        $this->manageTest
            ->shouldReceive('findByTeamName')
            ->with($teamName)
            ->andReturn([]);

        $this->manageProd
            ->shouldReceive('findByTeamName')
            ->with($teamName)
            ->andReturn([]);

        $entityList = $this->service->getEntityListForService($service);

        $this->assertEmpty($entityList->getEntities());
    }
}
