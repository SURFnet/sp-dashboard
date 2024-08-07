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
use stdClass;
use Surfnet\ServiceProviderDashboard\Application\Metadata\JsonGenerator\ArpGenerator;
use Surfnet\ServiceProviderDashboard\Application\Service\AttributeService;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Attribute;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Repository\AttributeRepository;

class ArpGeneratorTest extends MockeryTestCase
{
    /**
     * @var AttributeRepository
     */
    private $attributeRepository;

    /**
     * @return AttributeService
     */
    private $attributeService;

    public function setUp(): void
    {
        $this->attributeRepository = new AttributeRepository(__DIR__ . '/../fixture/attributes.json');
        $this->attributeService = new AttributeService($this->attributeRepository, 'en');
    }

    public function test_it_can_build_arp_metadata()
    {
        $entity = m::mock(ManageEntity::class)->makePartial();
        $attribute = m::mock(Attribute::class);
        $attribute->shouldReceive('getSource')->andReturn('idp');
        $attribute->shouldReceive('getValue')->andReturn('*');
        $attribute->shouldReceive('getReleaseAs')->andReturn('123');
        $attribute->shouldReceive('getUseAsNameId')->andReturn(false);
        $attribute->shouldReceive('hasMotivation')->andReturn(true);
        $attribute->shouldReceive('getMotivation')->andReturn('Motivation');

        $entity->shouldReceive('getAttributes->findAllByUrn')
            ->with('urn:mace:dir:attribute-def:displayName')
            ->andReturn([$attribute]);
        $entity->shouldReceive('getAttributes->findAllByUrn')
            ->with('urn:mace:dir:attribute-def:cn')
            ->andReturn([$attribute]);

        $entity->shouldReceive('getProtocol->getProtocol')
            ->andReturn('saml20');
        $entity->shouldReceive('getAttributes->getOriginalAttributes')->andReturn([]);
        $entity->shouldReceive('getAttributes->findAllByUrn')->andReturn([]);

        $factory = new ArpGenerator($this->attributeService);

        $metadata = $factory->build($entity);

        $this->assertCount(2, $metadata['attributes']);

        $this->assertNotEmpty($metadata['attributes']['urn:mace:dir:attribute-def:displayName']);
        $this->assertNotEmpty($metadata['attributes']['urn:mace:dir:attribute-def:cn']);
    }

    public function test_does_not_override_existing_manage_attributes_and_source_and_()
    {
        $factory = new ArpGenerator($this->attributeService);
        $manageEntity = $this->getManageEntity();
        $metadata = $factory->build($manageEntity);

        $this->assertCount(4, $metadata['attributes']);

        $this->assertNotEmpty($metadata['attributes']['urn:mace:dir:attribute-def:displayName']);
        $this->assertEquals('idp', $metadata['attributes']['urn:mace:dir:attribute-def:displayName'][0]['source']);
        $this->assertEquals('*', $metadata['attributes']['urn:mace:dir:attribute-def:displayName'][0]['value']);
        $this->assertEquals('456', $metadata['attributes']['urn:mace:dir:attribute-def:displayName'][0]['release_as']);
        $this->assertTrue($metadata['attributes']['urn:mace:dir:attribute-def:displayName'][0]['use_as_nameid']);

        $this->assertNotEmpty($metadata['attributes']['urn:mace:dir:attribute-def:cn']);
        $this->assertEquals('voot', $metadata['attributes']['urn:mace:dir:attribute-def:cn'][0]['source']);
        $this->assertEquals('*', $metadata['attributes']['urn:mace:dir:attribute-def:cn'][0]['value']);
        $this->assertEquals('123', $metadata['attributes']['urn:mace:dir:attribute-def:cn'][0]['release_as']);
        $this->assertFalse($metadata['attributes']['urn:mace:dir:attribute-def:cn'][0]['use_as_nameid']);

        $this->assertNotEmpty($metadata['attributes']['urn:mace:dir:attribute-def:givenName']);
        $this->assertEquals('idp', $metadata['attributes']['urn:mace:dir:attribute-def:givenName'][0]['source']);
        $this->assertEquals('*', $metadata['attributes']['urn:mace:dir:attribute-def:givenName'][0]['value']);
        $this->assertEquals('789', $metadata['attributes']['urn:mace:dir:attribute-def:givenName'][0]['release_as']);
        $this->assertFalse($metadata['attributes']['urn:mace:dir:attribute-def:givenName'][0]['use_as_nameid']);

        $this->assertNotEmpty($metadata['attributes']['urn:mace:dir:attribute-def:eduPersonEntitlement']);
        $this->assertEquals('sab', $metadata['attributes']['urn:mace:dir:attribute-def:eduPersonEntitlement'][0]['source']);
        $this->assertEquals('/^foobar(.*)$/i', $metadata['attributes']['urn:mace:dir:attribute-def:eduPersonEntitlement'][0]['value']);
        $this->assertEquals('123', $metadata['attributes']['urn:mace:dir:attribute-def:eduPersonEntitlement'][0]['release_as']);
        $this->assertFalse($metadata['attributes']['urn:mace:dir:attribute-def:eduPersonEntitlement'][0]['use_as_nameid']);

    }

    private function getManageEntity(string $protocol = 'sanl20', $registerAttributes = true)
    {
        $manageEntity = m::mock(ManageEntity::class);
        $manageEntity->shouldReceive('getProtocol->getProtocol')
            ->andReturn($protocol);

        $attributes = [];
        if ($registerAttributes) {
            $attributes = [
                $this->buildManageAttribute($manageEntity, 'urn:mace:dir:attribute-def:cn', 'voot', '*', '123', false),
                $this->buildManageAttribute($manageEntity, 'urn:mace:dir:attribute-def:displayName', 'idp', '*', '456', true),
                $this->buildManageAttribute($manageEntity, 'urn:mace:dir:attribute-def:givenName', 'idp', '*', '789', false),
                $this->buildManageAttribute($manageEntity, 'urn:mace:dir:attribute-def:eduPersonEntitlement', 'sab', '/^foobar(.*)$/i', '123', false),
            ];
            $manageEntity
                ->shouldReceive('getAttributes->findAllByUrn')
                ->with('urn:mace:dir:attribute-def:cn')
                ->andReturn([$attributes[0]]);
            $manageEntity
                ->shouldReceive('getAttributes->findAllByUrn')
                ->with('urn:mace:dir:attribute-def:displayName')
                ->andReturn([$attributes[1]]);
            $manageEntity
                ->shouldReceive('getAttributes->findAllByUrn')
                ->with('urn:mace:dir:attribute-def:givenName')
                ->andReturn([$attributes[2]]);
            $manageEntity
                ->shouldReceive('getAttributes->findAllByUrn')
                ->with('urn:mace:dir:attribute-def:eduPersonEntitlement')
                ->andReturn([$attributes[3]]);
        }
        $manageEntity
            ->shouldReceive('getAttributes->getAttributes')
            ->andReturn($attributes);
        $manageEntity
            ->shouldReceive('getAttributes->getOriginalAttributes')
            ->andReturn([]);

        // all the rest yield no search results
        $manageEntity
            ->shouldReceive('getAttributes->findAllByUrn')
            ->andReturn([]);
        $manageEntity
            ->shouldReceive('isManageEntity')
            ->andReturn(true);
        $manageEntity
            ->shouldReceive('getProtocol')
            ->andReturn('saml20');

        return $manageEntity;
    }

    private function buildManageAttribute(
        ManageEntity $manageEntity,
        string       $attributeName,
        string       $source,
        string       $value,
        string       $releaseAs,
        bool         $useAsNameId,
    )
    {
        $attribute = m::mock(Attribute::class);
        $attribute->shouldReceive('getName')
            ->andReturn($attributeName);
        $attribute->shouldReceive('getSource')
            ->andReturn($source);
        $attribute->shouldReceive('getValue')
            ->andReturn($value);
        $attribute->shouldReceive('getReleaseAs')
            ->andReturn($releaseAs);
        $attribute->shouldReceive('getUseAsNameId')
            ->andReturn($useAsNameId);
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

    public function test_does_not_add_epti_for_oidcng_entities()
    {
        $entity = $this->getManageEntity(Constants::TYPE_OPENID_CONNECT_TNG, false);

        $factory = new ArpGenerator($this->attributeService);

        $metadata = $factory->build($entity);

        // When no attributes are present, a stdClass is set as metadata attributes (for json encoding purposes)
        $this->assertEquals(new stdClass(), $metadata['attributes']);
    }
}
