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
use Surfnet\ServiceProviderDashboard\Application\Dto\MetadataConversionDto;
use Surfnet\ServiceProviderDashboard\Application\Metadata\JsonGenerator\SpDashboardMetadataGenerator;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Legacy\Repository\AttributesMetadataRepository;

class SpDashboardMetadataGeneratorTest extends MockeryTestCase
{
    public function test_it_can_build_sp_dashboard_metadata()
    {
        /** @var Entity $entity */
        $entity = m::mock(Entity::class)->makePartial();
        $service = new Service();

        $service->setTeamName('The A Team');
        $entity->setImportUrl('http://the-a-team.com/saml/metadata');

        $entity->setService($service);
        $entity = MetadataConversionDto::fromEntity($entity);
        $metadataRepository = new AttributesMetadataRepository(__DIR__ . '/../../../../../app/Resources');

        $factory = new SpDashboardMetadataGenerator($metadataRepository);

        $metadata = $factory->build($entity);

        $this->assertCount(2, $metadata);

        // Test some of the assertions
        $this->assertEquals('The A Team', $metadata['coin:service_team_id']);
        $this->assertEquals('http://the-a-team.com/saml/metadata', $metadata['coin:original_metadata_url']);
    }
}
