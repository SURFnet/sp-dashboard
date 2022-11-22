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

namespace Surfnet\ServiceProviderDashboard\Tests\Integration\Infrastructure\DashboardBundle\Validator\Constraints;

use GuzzleHttp\Handler\MockHandler;
use Mockery as m;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Validator\Constraints\ValidEntityId;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Validator\Constraints\ValidEntityIdValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class ValidEntityIdValidatorTest extends ConstraintValidatorTestCase
{
    /**
     * @var MockHandler
     */
    private $mockHandler;

    protected function tearDown(): void
    {
        parent::tearDown();
        m::close();
    }

    protected function createValidator()
    {
        $this->mockHandler = new MockHandler();
        return new ValidEntityIdValidator();
    }

    public function test_success()
    {
        $this->validator->validate('https://sub.domain.org', new ValidEntityId());

        $this->assertNoViolation();
    }

    public function test_empty_entity_id()
    {
        $this->validator->validate(null, new ValidEntityId());

        $violations = $this->context->getViolations();
        $this->assertCount(1, $violations);
        $this->assertEquals(
            'validator.entity_id.empty',
            $violations->get(0)->getMessageTemplate(),
            'Expected certain violation but dit not receive it.'
        );
    }

    public function test_invalid_domain()
    {
        $constraint = new ValidEntityId();
        $this->validator->validate('https:///invalid\.com', $constraint);

        $violations = $this->context->getViolations();
        $this->assertCount(1, $violations);
        $this->assertEquals(
            'validator.entity_id.invalid_entity_id',
            $violations->get(0)->getMessageTemplate(),
            'Expected certain violation but dit not receive it.'
        );
    }

    public function test_invalid_entity_id_url()
    {
        $constraint = new ValidEntityId();
        $this->validator->validate('q$:\₪.3%$', $constraint);

        $violations = $this->context->getViolations();
        $this->assertCount(1, $violations);
        $this->assertEquals(
            'validator.entity_id.invalid_entity_id',
            $violations->get(0)->getMessageTemplate(),
            'Expected certain violation but dit not receive it.'
        );
    }

    public function test_valid_urn()
    {
        $constraint = new ValidEntityId();
        $this->validator->validate('urn:mace:oclc.org:idm:metaauth:dev', $constraint);

        $this->assertNoViolation();
    }

    public function test_invalid_urn()
    {
        $constraint = new ValidEntityId();
        $this->validator->validate('urn:invalid', $constraint);

        $violations = $this->context->getViolations();
        $this->assertCount(1, $violations);
        $this->assertEquals(
            'validator.entity_id.invalid_entity_id',
            $violations->get(0)->getMessageTemplate(),
            'Expected certain violation but dit not receive it.'
        );
    }
}
