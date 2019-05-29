<?php

/**
 * Copyright 2018 SURFnet B.V.
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

use Mockery as m;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Validator\Constraints\ValidClientId;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Validator\Constraints\ValidClientIdValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class ValidClientIdValidatorTest extends ConstraintValidatorTestCase
{
    protected function tearDown()
    {
        parent::tearDown();
        m::close();
    }

    protected function createValidator()
    {
        return new ValidClientIdValidator();
    }

    public function test_success()
    {
        $this->validator->validate('https://sub.domain.org', new ValidClientId());

        $this->assertNoViolation();
    }

    public function test_success_for_production()
    {
        $this->validator->validate('https://sub.domain.org', new ValidClientId());

        $this->assertNoViolation();
    }

    public function test_empty_client_id()
    {
        $this->validator->validate(null, new ValidClientId());

        $violations = $this->context->getViolations();
        $this->assertCount(1, $violations);
        $this->assertEquals(
            'validator.client_id.empty',
            $violations->get(0)->getMessageTemplate(),
            'Expected certain violation but dit not receive it.'
        );
    }

    public function test_invalid_client_id_url()
    {
        $constraint = new ValidClientId();
        $this->validator->validate('q$:\₪.3%$', $constraint);

        $violations = $this->context->getViolations();
        $this->assertCount(1, $violations);
        $this->assertEquals(
            'validator.client_id.invalid_url',
            $violations->get(0)->getMessageTemplate(),
            'Expected certain violation but dit not receive it.'
        );
    }
}
