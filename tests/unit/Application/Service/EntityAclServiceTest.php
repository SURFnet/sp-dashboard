<?php

/**
 * Copyright 2019 SURFnet B.V.
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
use Surfnet\ServiceProviderDashboard\Application\Service\EntityAclService;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\IdentityProvider;
use Surfnet\ServiceProviderDashboard\Domain\Repository\IdentityProviderRepository;

class EntityAclServiceTest extends MockeryTestCase
{
    /**
     * @var Mock|IdentityProviderRepository
     */
    private $repository;

    /**
     * @var EntityAclService
     */
    private $service;

    public function setUp()
    {
        $this->repository = m::mock(IdentityProviderRepository::class);

        $this->service = new EntityAclService(
            $this->repository
        );
    }

    public function test_it_can_fetch_all_providers_and_return_them_sorted()
    {
        $idp0 = new IdentityProvider('0000', 'id0', 'name-0-nl', 'name-0-en');
        $idp1 = new IdentityProvider('0001', 'id1', 'name-1-nl', 'name-1-en');
        $idp2 = new IdentityProvider('0002', 'id2', 'name-2-nl', 'name-2-en');

        // find all returns IDP unsorted!
        $this->repository
            ->shouldReceive('findAll')
            ->andReturn([
                $idp1,
                $idp0,
                $idp2,
            ]);

        $providers = $this->service->getAvailableIdps();

        $this->assertCount(3, $providers);
        $this->assertSame($idp0, $providers[0]);
        $this->assertSame($idp1, $providers[1]);
        $this->assertSame($idp2, $providers[2]);
    }

    public function test_it_can_filter_allowed_providers_for_entity()
    {
        $idp2 = new IdentityProvider('0002', 'id2', 'name-2-nl', 'name-2-en');
        $idp1 = new IdentityProvider('0001', 'id1', 'name-1-nl', 'name-1-en');
        $idp0 = new IdentityProvider('0000', 'id0', 'name-0-nl', 'name-0-en');

        $entity = new Entity();
        $entity->setIdpWhitelist([$idp0, $idp2]);
        $entity->setIdpAllowAll(false);

        // find all returns IDP unsorted!
        $this->repository
            ->shouldReceive('findAll')
            ->andReturn([
                $idp1,
                $idp0,
                $idp2,
            ]);

        $providers = $this->service->getAllowedIdpsFromEntity($entity);

        $this->assertCount(2, $providers);
        $this->assertSame($idp0, $providers[0]);
        $this->assertSame($idp2, $providers[1]);
    }
}
