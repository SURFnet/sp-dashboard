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
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveSamlEntityCommand;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Validator\Constraints\HasAttributes;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Validator\Constraints\HasAttributesValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class HasAttributesValidatorTest extends ConstraintValidatorTestCase
{
    public function createValidator()
    {
        return new HasAttributesValidator();
    }

    public function test_success()
    {
        $attribute = new Attribute();
        $attribute->setRequested(true);

        $command = m::mock(SaveSamlEntityCommand::class);
        $command->makePartial();
        $command->shouldReceive('getGivenNameAttribute')->andReturn($attribute);

        $this->validator->validate($command, new HasAttributes());

        $this->assertNoViolation();
    }

    public function test_failure()
    {
        $attribute = new Attribute();
        $attribute->setRequested(false);

        $command = m::mock(SaveSamlEntityCommand::class);
        $command->makePartial();
        $command->shouldReceive('getGivenNameAttribute')->andReturn($attribute);

        $this->validator->validate($command, new HasAttributes());

        $violations = $this->context->getViolations();
        $violation = $violations->get(0);

        $this->assertEquals('validator.attribute.at_least_one_attribute_required', $violation->getMessageTemplate());
    }

    public function test_failure_null_values()
    {
        $command = m::mock(SaveSamlEntityCommand::class);
        $command->makePartial();
        $command->shouldReceive('getGivenNameAttribute')->andReturn(null);

        $this->validator->validate($command, new HasAttributes());

        $violations = $this->context->getViolations();
        $violation = $violations->get(0);

        $this->assertEquals('validator.attribute.at_least_one_attribute_required', $violation->getMessageTemplate());
    }
}
