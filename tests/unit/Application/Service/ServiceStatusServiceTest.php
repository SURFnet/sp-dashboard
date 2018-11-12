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
use Surfnet\ServiceProviderDashboard\Application\Service\EntityService;
use Surfnet\ServiceProviderDashboard\Application\Service\ServiceStatusService;
use Surfnet\ServiceProviderDashboard\Application\ViewObject\Entity as ViewObjectEntity;
use Surfnet\ServiceProviderDashboard\Application\ViewObject\EntityList;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Repository\PrivacyQuestionsRepository;
use Surfnet\ServiceProviderDashboard\Domain\Repository\ServiceRepository;

class ServiceStatusServiceTest extends MockeryTestCase
{
    /** @var ServiceRepository|m\MockInterface */
    private $repository;

    /**
     * @var EntityService|m\MockInterface
     */
    private $entityService;

    /**
     * @var ServiceStatusService
     */
    private $service;

    public function setUp()
    {
        $this->repository = m::mock(PrivacyQuestionsRepository::class);
        $this->entityService = m::mock(EntityService::class);
        $this->service = new ServiceStatusService($this->repository, $this->entityService);
    }

    /**
     * @dataProvider createEntityStatus
     * @param EntityList $entities
     * @param string $expectedStatus
     * @param string $dataProviderContext provides information on what test data was used
     */
    public function test_it_displays_correct_entity_status(EntityList $entities, $expectedStatus, $dataProviderContext)
    {
        $service = m::mock(Service::class);

        $this->entityService
            ->shouldReceive('getEntityListForService')
            ->with($service)
            ->andReturn($entities);

        $actualStatus = $this->service->getEntityStatus($service);
        $this->assertEquals($expectedStatus, $actualStatus, $dataProviderContext);
    }

    public function createEntityStatus()
    {
        return [
            [
                $this->buildEntityList([]),
                Service::ENTITY_PUBLISHED_NO,
                'No entities are available for this service, so none are published.',
            ],
            [
                $this->buildEntityList([0 => Entity::STATE_DRAFT]),
                Service::ENTITY_PUBLISHED_IN_PROGRESS,
                'One drafted entity should result in "in progress"',
            ],
            [
                $this->buildEntityList([0 => Entity::STATE_DRAFT, 1 => Entity::STATE_DRAFT, 2 => Entity::STATE_DRAFT]),
                Service::ENTITY_PUBLISHED_IN_PROGRESS,
                'Multiple drafted entity should result in "in progress"',
            ],
            [
                $this->buildEntityList([0 => Entity::STATE_PUBLISHED]),
                Service::ENTITY_PUBLISHED_YES,
                'One published entity should result in "yes"',
            ],
            [
                $this->buildEntityList([0 => Entity::STATE_PUBLISHED, 1 => Entity::STATE_PUBLISHED]),
                Service::ENTITY_PUBLISHED_YES,
                'Multiple published entity should result in "yes"',
            ],
            [
                $this->buildEntityList(
                    [0 => Entity::STATE_DRAFT, 1 => Entity::STATE_PUBLISHED, 2 => Entity::STATE_DRAFT]
                ),
                Service::ENTITY_PUBLISHED_YES,
                'Multiple mixed value published entity should result in "yes"',
            ],
        ];
    }

    private function buildEntityList(array $entities)
    {
        $entityList = [];
        foreach ($entities as $key => $status) {
            $mockEntity = m::mock(ViewObjectEntity::class);
            $mockEntity
                ->shouldReceive('getState')
                ->andReturn($status);

            $entityList[] = $mockEntity;
        }
        return new EntityList($entityList);
    }
}
