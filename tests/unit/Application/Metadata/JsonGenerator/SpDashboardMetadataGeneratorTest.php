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
use Surfnet\ServiceProviderDashboard\Application\Metadata\JsonGenerator\SpDashboardMetadataGenerator;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Coin;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\TypeOfServiceCollection;
use Surfnet\ServiceProviderDashboard\Legacy\Repository\AttributesMetadataRepository;

class SpDashboardMetadataGeneratorTest extends MockeryTestCase
{
    public function test_it_can_build_sp_dashboard_metadata()
    {
        $entity = m::mock(ManageEntity::class)->makePartial();
        $service = new Service();

        $service->setTeamName('The A Team');

        $coin = m::mock(Coin::class);
        $coin->shouldReceive('getOriginalMetadataUrl')->andReturn('http://the-a-team.com/saml/metadata');
        $coin->shouldReceive('getApplicationUrl')->andReturn(null);
        $coin->shouldReceive('getEula')->andReturn(null);
        $coin->shouldReceive('getTypeOfService')->andReturn(new TypeOfServiceCollection());
        $entity->shouldReceive('getMetaData->getCoin')->andReturn($coin);

        $entity->setService($service);
        $metadataRepository = new AttributesMetadataRepository(__DIR__ . '/../../../../../assets/Resources');

        $factory = new SpDashboardMetadataGenerator($metadataRepository);

        $metadata = $factory->build($entity);

        $this->assertCount(2, $metadata);

        // Test some of the assertions
        $this->assertEquals('The A Team', $metadata['coin:service_team_id']);
        $this->assertEquals('http://the-a-team.com/saml/metadata', $metadata['coin:original_metadata_url']);
    }
}
