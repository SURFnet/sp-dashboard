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

namespace Surfnet\ServiceProviderDashboard\Tests\Integration\Infrastructure\DashboardBundle\Validator\Constraints;

use Exception;
use Mockery as m;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveSamlEntityCommand;
use Surfnet\ServiceProviderDashboard\Domain\Repository\EntityRepository;
use Surfnet\ServiceProviderDashboard\Domain\Repository\QueryEntityRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Validator\Constraints\UniqueEntityId;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Validator\Constraints\UniqueEntityIdValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class UniqueEntityIdValidatorTest extends ConstraintValidatorTestCase
{
    /**
     * @var QueryEntityRepository
     */
    private $testClient;

    /**
     * @var QueryEntityRepository
     */
    private $prodClient;

    /**
     * @var EntityRepository
     */
    private $repository;

    protected function tearDown()
    {
        parent::tearDown();
        m::close();
    }

    protected function createValidator()
    {
        $this->repository = m::mock(EntityRepository::class);

        $this->testClient = m::mock(QueryEntityRepository::class);
        $this->prodClient = m::mock(QueryEntityRepository::class);

        return new UniqueEntityIdValidator(
            $this->testClient,
            $this->prodClient,
            $this->repository
        );
    }

    public function test_success()
    {
        $this->testClient->shouldReceive('findManageIdByEntityId')->andReturn(null);
        $this->prodClient->shouldNotReceive('findManageIdByEntityId');


        $entityCommand = m::mock(SaveSamlEntityCommand::class);
        $entityCommand->shouldReceive('isForProduction')->andReturn(false);
        $entityCommand->shouldReceive('getManageId')->andReturn(1);

        $this->mockFormData($entityCommand);

        $this->repository->shouldReceive('findById')
            ->andReturn(null);

        $this->validator->validate('https://sub.domain.org', new UniqueEntityId());

        $this->assertNoViolation();
    }

    public function test_success_for_production()
    {
        $this->testClient->shouldNotReceive('findManageIdByEntityId');
        $this->prodClient->shouldReceive('findManageIdByEntityId')->andReturn(null);

        $entityCommand = m::mock(SaveSamlEntityCommand::class);
        $entityCommand->shouldReceive('isForProduction')->andReturn(true);
        $entityCommand->shouldReceive('getManageId')->andReturn(1);

        $this->mockFormData($entityCommand);

        $this->repository->shouldReceive('findById')
            ->andReturn(null);

        $this->validator->validate('https://sub.domain.org', new UniqueEntityId());

        $this->assertNoViolation();
    }


    public function test_duplicate_entity_id()
    {
        $this->testClient->shouldReceive('findManageIdByEntityId')->andReturn('22222');
        $this->prodClient->shouldNotReceive('findManageIdByEntityId');

        $entityCommand = m::mock(SaveSamlEntityCommand::class);
        $entityCommand->shouldReceive('isForProduction')->andReturn(false);
        $entityCommand->shouldReceive('getManageId')->andReturn('11111');

        $this->mockFormData($entityCommand);

        $this->repository->shouldReceive('findById')
            ->andReturn(null);

        $this->validator->validate('https://sub.domain.org', new UniqueEntityId());

        $violations = $this->context->getViolations();
        $this->assertCount(1, $violations);
        $this->assertEquals(
            'validator.entity_id.already_exists',
            $violations->get(0)->getMessageTemplate(),
            'Expected certain violation but dit not receive it.'
        );
    }

    public function test_duplicate_entity_id_on_production()
    {
        $this->testClient->shouldNotReceive('findManageIdByEntityId');
        $this->prodClient->shouldReceive('findManageIdByEntityId')->andReturn('22222');

        $entityCommand = m::mock(SaveSamlEntityCommand::class);
        $entityCommand->shouldReceive('isForProduction')->andReturn(true);
        $entityCommand->shouldReceive('getManageId')->andReturn('11111');

        $this->mockFormData($entityCommand);

        $this->repository->shouldReceive('findById')
            ->andReturn(null);

        $this->validator->validate('https://sub.domain.org', new UniqueEntityId());

        $violations = $this->context->getViolations();
        $this->assertCount(1, $violations);
        $this->assertEquals(
            'validator.entity_id.already_exists',
            $violations->get(0)->getMessageTemplate(),
            'Expected certain violation but dit not receive it.'
        );
    }

    public function test_registry_exception()
    {
        $this->testClient->shouldReceive('findManageIdByEntityId')->andThrow(new Exception('An exception while fetching data from manage'));
        $this->prodClient->shouldNotReceive('findManageIdByEntityId');

        $entityCommand = m::mock(SaveSamlEntityCommand::class);
        $entityCommand->shouldReceive('isForProduction')->andReturn(false);

        $this->mockFormData($entityCommand);

        $this->validator->validate('https://sub.domain.org', new UniqueEntityId());

        $violations = $this->context->getViolations();
        $this->assertCount(1, $violations);
        $this->assertEquals(
            'validator.entity_id.registry_failure',
            $violations->get(0)->getMessageTemplate(),
            'Expected certain violation but dit not receive it.'
        );
    }

    private function mockFormData(SaveSamlEntityCommand $data)
    {
        $form = $this->createMock('Symfony\Component\Form\FormInterface');
        $form->expects($this->any())->method('getData')->willReturn($data);

        $this->setRoot($form);
    }
}
