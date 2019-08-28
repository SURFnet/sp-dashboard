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

namespace Surfnet\ServiceProviderDashboard\Tests\Integration\Application\CommandHandler\Entity;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\Mock;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveOidcngEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\Entity\SaveOidcngEntityCommandHandler;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Repository\EntityRepository;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Contact;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\OidcGrantType;

class SaveOidcngEntityCommandHandlerTest extends MockeryTestCase
{

    /**
     * @var SaveOidcngEntityCommandHandler
     */
    private $commandHandler;

    /**
     * @var EntityRepository&Mock
     */
    private $repository;

    public function setUp()
    {
        $this->repository = m::mock(EntityRepository::class);

        $this->commandHandler = new SaveOidcngEntityCommandHandler(
            $this->repository
        );
    }

    public function test_it_can_delete_an_entity_from_production()
    {
        $service = m::mock(Service::class);

        $command = SaveOidcngEntityCommand::forCreateAction($service, Entity::TYPE_OPENID_CONNECT_TNG);
        $command->setEntityId('test_entity');
        $command->setGrantType(OidcGrantType::GRANT_TYPE_IMPLICIT);
        $command->setRedirectUrls(['https://test.example.com/redirect', 'https://test.example.com/section/31/redirect']);
        $command->setNameEn('Test Entity');
        $command->setSupportContact(m::mock(Contact::class));
        $command->setAdministrativeContact(m::mock(Contact::class));
        $command->setTechnicalContact(m::mock(Contact::class));

        $command->setGivenNameAttribute(m::mock(Attribute::class));
        $command->setSurNameAttribute(m::mock(Attribute::class));
        $command->setCommonNameAttribute(m::mock(Attribute::class));
        $command->setDisplayNameAttribute(m::mock(Attribute::class));
        $command->setEmailAddressAttribute(m::mock(Attribute::class));
        $command->setOrganizationAttribute(m::mock(Attribute::class));
        $command->setOrganizationTypeAttribute(m::mock(Attribute::class));
        $command->setAffiliationAttribute(m::mock(Attribute::class));
        $command->setEntitlementAttribute(m::mock(Attribute::class));
        $command->setPrincipleNameAttribute(m::mock(Attribute::class));
        $command->setUidAttribute(m::mock(Attribute::class));
        $command->setPreferredLanguageAttribute(m::mock(Attribute::class));
        $command->setPersonalCodeAttribute(m::mock(Attribute::class));
        $command->setScopedAffiliationAttribute(m::mock(Attribute::class));

        $this->repository
            ->shouldReceive('isUnique')
            ->andReturn(true);

        $this->repository
            ->shouldReceive('save')
            ->with(
                m::on(
                    function (Entity $entity) {
                        // EPTI is not configurable on OIDC(ng) forms
                        $epti = $entity->getEduPersonTargetedIDAttribute();
                        $this->assertNull($epti);
                        $this->assertEquals('Test Entity', $entity->getNameEn());
                        // Without setting it explicitly 3600 is used as the default value
                        // See: https://www.pivotaltracker.com/story/show/167510474
                        $this->assertEquals(3600, $entity->getAccessTokenValidity());
                        return true;
                    }
                )
            );

        $this->commandHandler->handle($command);
    }
}
