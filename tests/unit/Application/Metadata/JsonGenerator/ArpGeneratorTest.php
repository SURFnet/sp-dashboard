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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Application\Metadata\JsonGenerator;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Surfnet\ServiceProviderDashboard\Application\Metadata\JsonGenerator\ArpGenerator;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Attribute;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Legacy\Repository\AttributesMetadataRepository;

class ArpGeneratorTest extends MockeryTestCase
{
    public function test_it_can_build_arp_metadata()
    {
        $entity = m::mock(ManageEntity::class)->makePartial();
        $attribute = m::mock(Attribute::class);
        $attribute->shouldReceive('hasMotivation');

        $entity->shouldReceive('getAttributes->findByUrn')
            ->with('urn:mace:dir:attribute-def:displayName')
            ->andReturn($attribute);
        $entity->shouldReceive('getAttributes->findByUrn')
            ->with('urn:mace:dir:attribute-def:cn')
            ->andReturn($attribute);
        $entity->shouldReceive('getProtocol->getProtocol')
            ->andReturn('saml20');

        $entity->shouldReceive('getAttributes->findByUrn');
        $metadataRepository = new AttributesMetadataRepository(__DIR__ . '/../../../../../app/Resources');

        $factory = new ArpGenerator($metadataRepository);

        $metadata = $factory->build($entity);

        $this->assertCount(2, $metadata['attributes']);

        $this->assertNotEmpty($metadata['attributes']['urn:mace:dir:attribute-def:displayName']);
        $this->assertNotEmpty($metadata['attributes']['urn:mace:dir:attribute-def:cn']);
    }

    public function test_does_not_override_existing_manage_attributes_and_sources()
    {
        $metadataRepository = new AttributesMetadataRepository(__DIR__ . '/../../../../../app/Resources');

        $factory = new ArpGenerator($metadataRepository);
        $manageEntity = $this->getManageEntity();
        $metadata = $factory->build($manageEntity);

        $this->assertCount(4, $metadata['attributes']);

        $this->assertNotEmpty($metadata['attributes']['urn:mace:dir:attribute-def:displayName']);
        $this->assertEquals('idp', $metadata['attributes']['urn:mace:dir:attribute-def:displayName'][0]['source']);
        $this->assertEquals('*', $metadata['attributes']['urn:mace:dir:attribute-def:displayName'][0]['value']);
        $this->assertNotEmpty($metadata['attributes']['urn:mace:dir:attribute-def:cn']);
        $this->assertEquals('idp', $metadata['attributes']['urn:mace:dir:attribute-def:cn'][0]['source']);
        $this->assertEquals('*', $metadata['attributes']['urn:mace:dir:attribute-def:cn'][0]['value']);
        $this->assertNotEmpty($metadata['attributes']['urn:mace:dir:attribute-def:manage-1']);
        $this->assertEquals('idp', $metadata['attributes']['urn:mace:dir:attribute-def:manage-1'][0]['source']);
        $this->assertEquals('*', $metadata['attributes']['urn:mace:dir:attribute-def:manage-1'][0]['value']);
        $this->assertNotEmpty($metadata['attributes']['urn:mace:dir:attribute-def:manage-2']);
        $this->assertEquals('sab', $metadata['attributes']['urn:mace:dir:attribute-def:manage-2'][0]['source']);
        $this->assertEquals('/^foobar(.*)$/i', $metadata['attributes']['urn:mace:dir:attribute-def:manage-2'][0]['value']);
    }

    public function test_adds_epti_for_oidcng_entities()
    {
        $entity = $this->getManageEntity(Constants::TYPE_OPENID_CONNECT_TNG, false);

        $metadataRepository = new AttributesMetadataRepository(__DIR__ . '/../../../../../app/Resources');
        $factory = new ArpGenerator($metadataRepository);

        $metadata = $factory->build($entity);

        $this->assertCount(1, $metadata['attributes']);
        $this->assertNotEmpty($metadata['attributes']['urn:mace:dir:attribute-def:eduPersonTargetedID']);
    }

    private function getManageEntity(string $protocol = 'sanl20', $registerAttributes = true)
    {
        $manageEntity = m::mock(ManageEntity::class);
        $manageEntity->shouldReceive('getProtocol->getProtocol')
            ->andReturn($protocol);

        $attributes = [];
        if ($registerAttributes) {
            $attributes = [
                $this->buildManageAttribute($manageEntity, 'urn:mace:dir:attribute-def:cn', 'voot', '*'),
                $this->buildManageAttribute($manageEntity, 'urn:mace:dir:attribute-def:displayName', 'idp', '*'),
                $this->buildManageAttribute($manageEntity, 'urn:mace:dir:attribute-def:manage-1', 'idp', '*'),
                $this->buildManageAttribute($manageEntity, 'urn:mace:dir:attribute-def:manage-2', 'sab', '/^foobar(.*)$/i'),
            ];
        }
        $manageEntity
            ->shouldReceive('getAttributes->getAttributes')
            ->andReturn($attributes);

        $manageEntity
            ->shouldReceive('getAttributes->findByUrn');
        $manageEntity
            ->shouldReceive('isManageEntity')
            ->andReturn(true);
        $manageEntity
            ->shouldReceive('getProtocol')
            ->andReturn('saml20');

        return $manageEntity;
    }

    private function buildManageAttribute(ManageEntity $manageEntity, string $attributeName, string $source, string $value)
    {

        $attribute = m::mock(Attribute::class);
        $attribute->shouldReceive('getName')
            ->andReturn($attributeName);
        $attribute->shouldReceive('getSource')
            ->andReturn($source);
        $attribute->shouldReceive('getValue')
            ->andReturn($value);
        $attribute->shouldReceive('getMotivation')
            ->andReturn('The Manage motivation');
        $attribute->shouldReceive('hasMotivation')
            ->andReturn(true);

        $manageEntity
            ->shouldReceive('getAttributes->findByUrn')
            ->with($attributeName)
            ->andReturn($attribute);

        return $attribute;
    }
}
