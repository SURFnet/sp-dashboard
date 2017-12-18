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
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute;
use Surfnet\ServiceProviderDashboard\Legacy\Repository\AttributesMetadataRepository;

class ArpGeneratorTest extends MockeryTestCase
{
    public function test_it_can_build_arp_metadata()
    {
        /** @var Entity $entity */
        $entity = m::mock(Entity::class)->makePartial();

        $attribute = new Attribute();
        $attribute->setRequested(true);
        $attribute->setMotivation('commonname');
        $entity->setCommonNameAttribute($attribute);

        $attribute = new Attribute();
        $attribute->setRequested(true);
        $attribute->setMotivation('displayname');
        $entity->setDisplayNameAttribute($attribute);

        $metadataRepository = new AttributesMetadataRepository(__DIR__ . '/../../../../../app/Resources');

        $factory = new ArpGenerator($metadataRepository);

        $metadata = $factory->build($entity);

        $this->assertCount(2, $metadata);

        $this->assertNotEmpty($metadata['attributes']['urn:mace:dir:attribute-def:displayName']);
        $this->assertNotEmpty($metadata['attributes']['urn:mace:dir:attribute-def:cn']);
    }
}
