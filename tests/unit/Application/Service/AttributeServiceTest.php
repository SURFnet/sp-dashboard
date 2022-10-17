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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Application\Service;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Surfnet\ServiceProviderDashboard\Application\Service\AttributeService;
use Surfnet\ServiceProviderDashboard\Application\ViewObject\EntityDetailAttribute;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Attribute;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\AttributeList;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Dto\Attribute as AttributeDto;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Dto\NullAttribute;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Repository\AttributeRepositoryInterface;

class AttributeServiceTest extends TestCase
{
    private $service;

    private $repository;

    public function setUp()
    {
        $this->repository = m::mock(AttributeRepositoryInterface::class);
        $this->service = new AttributeService($this->repository, 'en');
    }

    public function test_it_can_create_entity_detail_attributes()
    {
        $attributeList = m::mock(AttributeList::class);
        $attribute1 = m::mock(Attribute::class);
        $attribute1->shouldReceive('getName')->andReturn('1');
        $attribute1->shouldReceive('getMotivation')->andReturn('Motivation 1');
        $attribute2 = m::mock(Attribute::class);
        $attribute2->shouldReceive('getName')->andReturn('2');
        $attribute2->shouldReceive('getMotivation')->andReturn('Motivation 2');

        $dto = AttributeDto::fromAttribute($this->getDtoData());

        $this->repository
            ->shouldReceive('findOneByName')
            ->andReturn($dto);

        $attributeList->shouldReceive('getAttributes')
            ->andReturn([[$attribute1], [$attribute2]]);
        $attributes = $this->service->createEntityDetailAttributes($attributeList);
        $this->assertCount(2, $attributes);
        $this->assertInstanceOf(EntityDetailAttribute::class, $attributes[0]);
    }

    public function test_it_only_adds_existing_attributes()
    {
        $attributeList = m::mock(AttributeList::class);
        $attribute1 = m::mock(Attribute::class);
        $attribute1->shouldReceive('getName')->andReturn('1');
        $attribute1->shouldReceive('getMotivation')->andReturn('Motivation 1');
        $attribute2 = m::mock(Attribute::class);
        $attribute2->shouldReceive('getName')->andReturn('2');
        $attribute2->shouldReceive('getMotivation')->andReturn('Motivation 2');

        $dto = AttributeDto::fromAttribute($this->getDtoData());

        $this->repository
            ->shouldReceive('findOneByName', 'findOneByName')
            ->andReturn($dto, new NullAttribute());

        $attributeList->shouldReceive('getAttributes')
            ->andReturn([[$attribute1], [$attribute2]]);
        $attributes = $this->service->createEntityDetailAttributes($attributeList);
        $this->assertCount(1, $attributes);
        $this->assertInstanceOf(EntityDetailAttribute::class, $attributes[0]);
    }

    private function getDtoData()
    {
        return [
            'id' => 'emailAddress',
            'form' => [
                'translations' => [
                    'en' => [
                        'label' => 'emailAddressAttribute',
                        'info' => 'Description is placed here'
                    ]
                ]
            ],
            'detail' => [
                'en' => [
                    'label' => 'emailAddressAttribute',
                    'info' => 'Description is placed here'
                ]
            ],
            'urns' => [
                'urn:mace:dir:attribute-def:mail',
                'urn:oid:0.9.2342.19200300.100.1.3'
            ]
        ];
    }
}
