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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Application\Factory;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Surfnet\ServiceProviderDashboard\Application\Factory\EntityDetailFactory;
use Surfnet\ServiceProviderDashboard\Application\Service\AttributeServiceInterface;
use Surfnet\ServiceProviderDashboard\Application\ViewObject\EntityDetail;
use Surfnet\ServiceProviderDashboard\Application\ViewObject\EntityDetailAttribute;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;

class EntityDetailFactoryTest extends TestCase
{
    private $factory;

    private $attributeService;

    public function setUp()
    {
        $this->attributeService = m::mock(AttributeServiceInterface::class);
        $this->factory = new EntityDetailFactory($this->attributeService, 'https://test.uri', 'https://prod.uri');
    }

    public function test_it_builds_from_manage_entity()
    {
        $manageEntity = $this->loadManageEntity();
        $entityDetailAttributes = [
            $this->loadEntityDetails('urn1'),
            $this->loadEntityDetails('urn2'),
        ];
        $this->attributeService
            ->shouldReceive('createEntityDetailAttributes')
            ->andReturn($entityDetailAttributes);
        $detail = $this->factory->buildFrom($manageEntity);
        self::assertInstanceOf(EntityDetail::class, $detail);
        self::assertCount(2, $detail->getAttributes());
    }

    private function loadManageEntity(): ManageEntity
    {
        $data = json_decode(file_get_contents(__DIR__ . '/fixture/saml20_sp_response.json'), true);
        $service = m::mock(Service::class);
        $service->shouldReceive('getId')->andReturn(31);
        $entity = ManageEntity::fromApiResponse($data);
        $entity->setService($service);
        $entity->setEnvironment('test');
        return $entity;
    }

    private function loadEntityDetails(string $urn): EntityDetailAttribute
    {
        $attribute = new EntityDetailAttribute();
        $attribute->label = $urn;
        $attribute->informationPopup = 'Information popup for ' . $urn;
        $attribute->value = 'Value for ' . $urn;
        return $attribute;
    }
}
