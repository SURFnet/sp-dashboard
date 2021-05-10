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
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveEntityCommandInterface;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveOauthClientCredentialClientCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveOidcngEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveOidcngResourceServerEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveSamlEntityCommand;
use Surfnet\ServiceProviderDashboard\Domain\Repository\EntityRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Validator\Constraints\UniqueEntityId;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Validator\Constraints\UniqueEntityIdValidator;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Service\ManageQueryService;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class UniqueEntityIdValidatorTest extends ConstraintValidatorTestCase
{
    /**
     * @var m\MockInterface&ManageQueryService
     */
    private $queryService;

    protected function tearDown()
    {
        parent::tearDown();
        m::close();
    }

    protected function createValidator()
    {
        $this->queryService = m::mock(ManageQueryService::class);

        return new UniqueEntityIdValidator($this->queryService);
    }

    public function test_success()
    {
        $this->queryService->shouldReceive('test', 'findManageIdByEntityId')->andReturn(null);

        $entityCommand = m::mock(SaveSamlEntityCommand::class);
        $entityCommand->shouldReceive('isForProduction')->andReturn(false);
        $entityCommand->shouldReceive('getManageId')->andReturn(1);

        $this->mockFormData($entityCommand);

        $this->validator->validate('https://sub.domain.org', new UniqueEntityId());

        $this->assertNoViolation();
    }

    /**
     * @dataProvider validClientIdCommands
     */
    public function test_success_client_ids($command)
    {
        $this->queryService->shouldReceive('test', 'findManageIdByEntityId')->andReturn(null);
        $this->mockFormData($command);
        $this->validator->validate('https://sub.domain.org', new UniqueEntityId());
        $this->assertNoViolation();
    }

    public function test_success_for_production()
    {
        $this->queryService->shouldReceive('production', 'findManageIdByEntityId')->andReturn(null);

        $entityCommand = m::mock(SaveSamlEntityCommand::class);
        $entityCommand->shouldReceive('isForProduction')->andReturn(true);
        $entityCommand->shouldReceive('getManageId')->andReturn(1);

        $this->mockFormData($entityCommand);
        $this->validator->validate('https://sub.domain.org', new UniqueEntityId());

        $this->assertNoViolation();
    }


    public function test_duplicate_entity_id()
    {
        $this->queryService->shouldReceive('test', 'findManageIdByEntityId')->andReturn('2222222');

        $entityCommand = m::mock(SaveSamlEntityCommand::class);
        $entityCommand->shouldReceive('isForProduction')->andReturn(false);
        $entityCommand->shouldReceive('getManageId')->andReturn('11111');

        $this->mockFormData($entityCommand);
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
        $this->queryService->shouldReceive('findManageIdByEntityId')->andReturn('2222222');

        $entityCommand = m::mock(SaveSamlEntityCommand::class);
        $entityCommand->shouldReceive('isForProduction')->andReturn(true);
        $entityCommand->shouldReceive('getManageId')->andReturn('11111');

        $this->mockFormData($entityCommand);

        $this->validator->validate('https://sub.domain.org', new UniqueEntityId());

        $violations = $this->context->getViolations();
        $this->assertCount(1, $violations);
        $this->assertEquals(
            'validator.entity_id.already_exists',
            $violations->get(0)->getMessageTemplate(),
            'Expected certain violation but dit not receive it.'
        );
    }

    public function test_oidcng_client_id_checked_without_protocol()
    {
        $this->queryService->shouldReceive('findManageIdByEntityId')->with('test', 'sub.domain.org')->andReturn(null);

        $entityCommand = m::mock(SaveOidcngResourceServerEntityCommand::class);
        $entityCommand->shouldReceive('isForProduction')->andReturn(false);
        $entityCommand->shouldReceive('getManageId')->andReturn(1);

        $this->mockFormData($entityCommand);

        $this->validator->validate('https://sub.domain.org', new UniqueEntityId());

        $this->assertNoViolation();
    }

    public function test_registry_exception()
    {

        $this->queryService->shouldReceive('test', 'findManageIdByEntityId')->andThrow(new Exception('An exception while fetching data from manage'));

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

    private function mockFormData(SaveEntityCommandInterface $data)
    {
        $form = $this->createMock('Symfony\Component\Form\FormInterface');
        $form->expects($this->any())->method('getData')->willReturn($data);

        $this->setRoot($form);
    }

    public function validClientIdCommands()
    {
        $entityCommand = m::mock(SaveOidcngResourceServerEntityCommand::class);
        $entityCommand->shouldReceive('isForProduction')->andReturn(false);
        $entityCommand->shouldReceive('getManageId')->andReturn(1);
        yield [$entityCommand];

        $entityCommand = m::mock(SaveOauthClientCredentialClientCommand::class);
        $entityCommand->shouldReceive('isForProduction')->andReturn(false);
        $entityCommand->shouldReceive('getManageId')->andReturn(1);
        yield [$entityCommand];

        $entityCommand = m::mock(SaveOidcngEntityCommand::class);
        $entityCommand->shouldReceive('isForProduction')->andReturn(false);
        $entityCommand->shouldReceive('getManageId')->andReturn(1);
        yield [$entityCommand];
    }
}
