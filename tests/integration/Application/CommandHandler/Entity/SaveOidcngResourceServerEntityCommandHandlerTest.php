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
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveOidcngResourceServerEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\Entity\SaveOidcngResourceServerEntityCommandHandler;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Repository\EntityRepository;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Contact;

class SaveOidcngResourceServerEntityCommandHandlerTest extends MockeryTestCase
{

    /**
     * @var SaveOidcngResourceServerEntityCommandHandler
     */
    private $commandHandler;

    /**
     * @var EntityRepository&Mock
     */
    private $repository;

    public function setUp()
    {
        $this->repository = m::mock(EntityRepository::class);

        $this->commandHandler = new SaveOidcngResourceServerEntityCommandHandler(
            $this->repository
        );
    }

    public function test_it_can_handle_saving_of_a_save_command()
    {
        $service = m::mock(Service::class);

        $command = SaveOidcngResourceServerEntityCommand::forCreateAction($service);
        $command->setEntityId('test_entity');
        $command->setNameEn('Test Entity');
        $command->setSupportContact(m::mock(Contact::class));
        $command->setAdministrativeContact(m::mock(Contact::class));
        $command->setTechnicalContact(m::mock(Contact::class));


        $this->repository
            ->shouldReceive('isUnique')
            ->andReturn(true);

        $this->repository
            ->shouldReceive('save')
            ->with(
                m::on(
                    function (Entity $entity) {
                        $this->assertEquals('test_entity', $entity->getEntityId());
                        $this->assertEquals('Test Entity', $entity->getNameEn());
                        return true;
                    }
                )
            );

        $this->commandHandler->handle($command);
    }
}
