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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Application\Factory;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Surfnet\ServiceProviderDashboard\Application\Factory\MotivationMetadataFactory;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute;
use Surfnet\ServiceProviderDashboard\Legacy\Repository\AttributesMetadataRepository;

class MotivationMetadataFactoryTest extends MockeryTestCase
{
    public function test_it_can_build_motivation_metadata()
    {
        /** @var Entity $entity */
        $entity = m::mock(Entity::class)->makePartial();
        $service = m::mock(Service::class)->makePartial();

        $entity->setAffiliationAttribute($this->buildAttribute('Motivation 1', true));
        $entity->setEduPersonTargetedIDAttribute($this->buildAttribute('Motivation 2', true));
        $entity->setUidAttribute($this->buildAttribute('Motivation 3', true));
        $entity->setSurNameAttribute($this->buildAttribute('Motivation 4', true));
        $entity->setPrincipleNameAttribute($this->buildAttribute('Motivation 5', false));
        $entity->setEntitlementAttribute($this->buildAttribute('Motivation 6', false));

        $entity->setService($service);

        $metadataRepository = new AttributesMetadataRepository(__DIR__ . '/../../../../app/Resources');

        $factory = new MotivationMetadataFactory($metadataRepository);

        $metadata = $factory->build($entity);

        $this->assertCount(4, $metadata);

        // Test some of the assertions
        $this->assertEquals('Motivation 1', $metadata['metaDataFields.coin:attr_motivation:eduPersonAffiliation']);
        $this->assertEquals('Motivation 2', $metadata['metaDataFields.coin:attr_motivation:eduPersonTargetedID']);
        $this->assertEquals('Motivation 3', $metadata['metaDataFields.coin:attr_motivation:uid']);
        $this->assertEquals('Motivation 4', $metadata['metaDataFields.coin:attr_motivation:sn']);
    }

    /**
     * @param string $motivation
     * @param bool $requested
     *
     * @return Attribute
     */
    private function buildAttribute($motivation, $requested)
    {
        $attr = new Attribute();
        $attr->setMotivation($motivation);
        $attr->setRequested($requested);
        return $attr;
    }
}
